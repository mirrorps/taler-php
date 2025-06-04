<?php

namespace Taler\Http;

use League\Uri\Uri;
use Taler\Http\Response as TalerResponse;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use PsrDiscovery\Discover;

class HttpClientWrapper
{
    /** @var string */
    protected $userAgent = 'Mirrorps_Taler_PHP (https://github.com/mirrorps/taler-php)';

    /**
     * @param TalerConfig $config
     * @param ClientInterface|null $client
     * @param bool $wrapResponse
     */
    public function __construct(
        private TalerConfig $config,
        private ?ClientInterface $client = null,
        private ?RequestFactoryInterface $requestFactory = null,
        private ?StreamFactoryInterface $streamFactory = null,
        public bool $wrapResponse = true
    )
    {
        $this->client = $client ?? Discover::httpClient();
        $this->requestFactory = $requestFactory ?? Discover::httpRequestFactory();
        $this->streamFactory = $streamFactory ?? Discover::httpStreamFactory();

        if (!$this->client || !$this->requestFactory || !$this->streamFactory) {
            throw new \RuntimeException(
                'Required PSR-18 HTTP Client or PSR-17 Factory implementations not found. ' .
                'Please install a compatible package (e.g., guzzlehttp/guzzle) or provide your own implementations.'
            );
        }

    }

    /**
     * Send a HTTP request.
     *
     * @param string $method HTTP method (GET, POST, etc.)
     * @param string $endpoint Endpoint path (relative to base URL)
     * @param array<string, string|string[]> $headers
     * @param string|null $body Request body, if any
     * @return ResponseInterface
     */
    public function request(
        string $method,
        string $endpoint,
        array $headers = [],
        ?string $body = null
    ): ResponseInterface
    {
        $request = $this->createRequest($method, $endpoint, $headers, $body);

        try {
            if ($this->wrapResponse) {
                return new TalerResponse($this->client->sendRequest($request));
            }

            return $this->client->sendRequest($request);
        } catch (\Throwable $e) {
            if ($this->wrapResponse) {
                throw new TalerException($e->getMessage(), $e->getCode());
            }

            throw $e;
        }
    }

    /**
     * Send a HTTP request.
     *
     * @param string $method HTTP method (GET, POST, etc.)
     * @param string $endpoint Endpoint path (relative to base URL)
     * @param array<string, string|string[]> $headers Additional headers
     * @param string|null $body Request body, if any
     * @return mixed
     */
    public function requestAsync(
        string $method,
        string $endpoint,
        array $headers = [],
        ?string $body = null
    )
    {
        if (!$this->client instanceof \Http\Client\HttpAsyncClient) {
            throw new \RuntimeException(
                'The provided HTTP client does not support async requests.'
            );
        }

        $request = $this->createRequest($method, $endpoint, $headers, $body);

        try {
            return $this->client->sendAsyncRequest($request);
        } catch (\Throwable $e) {
            if ($this->wrapResponse) {
                throw new TalerException($e->getMessage(), $e->getCode());
            }

            throw $e;
        }
    }

    /**
     * Creates a HTTP request.
     *
     * @param string $method HTTP method (GET, POST, etc.)
     * @param string $endpoint Endpoint path (relative to base URL)
     * @param array<string, string|string[]> $headers Additional headers
     * @param string|null $body Request body, if any
     * @return RequestInterface
     */

    private function createRequest(
        string $method,
        string $endpoint,
        array $headers,
        ?string $body = null
    ): RequestInterface
    {
        $url = $this->buildUrl($endpoint);

        $headers['User-Agent'] = $this->userAgent;

        if ($authToken = $this->config->getAuthToken()) {
            $headers['Authorization'] = $authToken;
        }

        if ($body !== null && !isset($headers['Content-Type'])) {
            $headers['Content-Type'] = 'application/json';
        }

        $request = $this->requestFactory->createRequest($method, $url);

        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        if ($body !== null) {
            $stream = $this->streamFactory->createStream($body);
            $request = $request->withBody($stream);
        }

        return $request;
    }

    private function buildUrl(string $endpoint): string
    {
        try {
            // Resolve the endpoint URI against the base URI.
            // Uri::fromBaseUri handles path normalization (e.g., removing dot segments like "/./", "/../")
            // and resolves the endpoint relative to the base URI according to RFC 3986.
            $finalUrl = Uri::fromBaseUri($endpoint, $this->getBaseUrl());

            $this->validateFinalUrl($endpoint, $finalUrl);

            return $finalUrl->__toString();

        } catch (\League\Uri\Contracts\UriException | \InvalidArgumentException $e) {
            throw new TalerException('Failed to build URL: ' . $e->getMessage(), 0, $e);
        }
    }

    private function validateFinalUrl(string $endpoint, Uri $finalUrl): void
    {
        if (strpos($endpoint, '%2F') !== false || strpos($endpoint, '%2f') !== false) {
            throw new \InvalidArgumentException('Encoded slashes are not allowed in endpoints.');
        }

        // Ensure the resolved URI is still "under" the original base URI's scheme, authority, and path prefix.
        $baseUriForCheck = Uri::new($this->getBaseUrl());
        $baseUriPrefixString = $baseUriForCheck->withPath($baseUriForCheck->getPath())->__toString();

        if (strpos($finalUrl->__toString(), $baseUriPrefixString) !== 0) {
             throw new \InvalidArgumentException('Endpoint results in a URL outside the configured base path. Resolved URL: ' . $finalUrl->__toString() . ', Base prefix: ' . $baseUriPrefixString);
        }
    }

    private function getBaseUrl(): string
    {
        return rtrim($this->config->getBaseUrl(), '/') . '/';
    }
}

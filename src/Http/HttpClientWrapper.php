<?php

namespace Taler\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Promise\PromiseInterface;
use Taler\Http\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;

class HttpClientWrapper
{
    /** @var string */
    protected $userAgent = 'Mirrorps_Taler_PHP (https://github.com/mirrorps/taler-php)';

    /**
     * @param TalerConfig $config
     * @param ClientInterface|null $client
     * @param array<string, mixed> $clientOptions
     * @param bool $wrapResponse
     */
    public function __construct(
        private TalerConfig $config,
        private ?ClientInterface $client = null,
        private array $clientOptions = [],
        public bool $wrapResponse = true
    )
    {
        $this->loadClient($client);
        $this->ensureClientSupported();
    }

    /**
     * @param string $method The HTTP request verb
     * @param string $endpoint The Taler API endpoint
     * @param array<string, mixed> $options An array of options for the request
     * @return ResponseInterface
     * @throws TalerException When request fails and wrapResponse is true
     * @throws \RuntimeException When client does not support request method
     * @throws \Throwable When request fails and wrapResponse is false
     */
    public function request(string $method, string $endpoint, array $options = []): ResponseInterface
    {
        $url = $this->buildUrl($endpoint);

        $options = array_merge($this->clientOptions, $options);
        $options['headers']['User-Agent'] = $this->userAgent;

        if ($auth = $this->config->getAuthToken()) {
            $options['headers']['Authorization'] = $auth;
        }

        $request = new \GuzzleHttp\Psr7\Request($method, $url, $options['headers'] ?? [], $options['body'] ?? null);

        try {
            if ($this->wrapResponse) {
                return new Response($this->client->sendRequest($request));
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
     * @param string $method The HTTP request verb
     * @param string $endpoint The Taler API endpoint
     * @param array<string, mixed> $options An array of options for the request
     * @return PromiseInterface
     * @throws \RuntimeException When async requests are not supported
     */
    public function requestAsync(string $method, string $endpoint, array $options = []): PromiseInterface
    {
        $this->ensureAsyncSupported();

        $url = $this->buildUrl($endpoint);

        $options = array_merge($this->clientOptions, $options);
        $options['headers']['User-Agent'] = $this->userAgent;

        if ($auth = $this->config->getAuthToken()) {
            $options['headers']['Authorization'] = $auth;
        }

        /**
         * Note: This type hint is added to help static analysis tools (like PHPStan or Psalm)
         * correctly infer that $client is a \GuzzleHttp\Client instance.
         *
         * PSR-* HTTP client interfaces (like PSR-18's ClientInterface) do not define
         * a requestAsync() method - this is only available on Guzzle's client.
         *
         * @var \GuzzleHttp\Client $client
         */
        $client = $this->client;

        $promise = $client->requestAsync($method, $url, $options);

        if ($this->wrapResponse) {
            return $promise->then(
                fn($response) => new Response($response),
                fn($e) => throw new TalerException($e->getMessage(), $e->getCode())
            );
        }

        return $promise;
    }

    private function buildUrl(string $endpoint): string
    {
        return rtrim($this->config->getBaseUrl(), '/') . '/' . ltrim($endpoint, '/');
    }

    /**
     * @param ClientInterface|null $client
     */
    private function loadClient(?ClientInterface $client): void
    {
        $this->client = $client ?? new GuzzleClient();
    }

    private function ensureClientSupported(): void
    {
        if($this->client instanceof ClientInterface) {
            return;
        }

        throw new \RuntimeException('Http Client must implement ClientInterface');
    }

    private function ensureAsyncSupported(): void
    {
        if (method_exists($this->client, 'requestAsync')) {
            return;
        }

        throw new \RuntimeException('The configured HTTP client does not support async requests (missing requestAsync).');
    }
}

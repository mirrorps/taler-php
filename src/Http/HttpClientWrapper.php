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
     * @param string $method   The HTTP request verb
     * @param string $endpoint The Taler API endpoint
     * @param array $options   An array of options for the request
     * @return ResponseInterface
     */
    public function request(string $method, string $endpoint, array $options = [])
    {
        $url = $this->buildUrl($endpoint);

        $options = array_merge($this->clientOptions, $options);
        $options['headers']['User-Agent'] = $this->userAgent;

        if ($auth = $this->config->getAuthToken()) {
            $options['headers']['Authorization'] = $auth;
        }

        try {
            if ($this->wrapResponse) {
                return new Response($this->client->request($method, $url, $options));
            }

            return $this->client->request($method, $url, $options);
        } catch (\Throwable $e) {

            if ($this->wrapResponse) {
                throw new TalerException($e->getMessage(), $e->getCode());
            }

            throw $e;
        }
    }

    public function requestAsync(string $method, string $endpoint, array $options = []): PromiseInterface
    {
        $this->ensureAsyncSupported();

        $url = $this->buildUrl($endpoint);

        $options = array_merge($this->clientOptions, $options);
        $options['headers']['User-Agent'] = $this->userAgent;

        if ($auth = $this->config->getAuthToken()) {
            $options['headers']['Authorization'] = $auth;
        }

        $promise = $this->client->requestAsync($method, $url, $options);

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


    private function loadClient($client): void
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
        if (method_exists($this->client, 'sendAsync')) {
            return;
        }

        throw new \RuntimeException('The configured HTTP client does not support async requests (missing sendAsync).');
    }

}

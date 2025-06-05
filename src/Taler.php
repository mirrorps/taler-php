<?php

namespace Taler;

use Psr\Http\Client\ClientInterface;
use Taler\API\Exchange\ExchangeClient;
use Taler\Config\TalerConfig;
use Taler\Http\HttpClientWrapper;

class Taler
{
    protected HttpClientWrapper $httpClientWrapper;

    /**
     * @param TalerConfig $config
     * @param ClientInterface|null $client
     * @param array<string, mixed> $clientOptions
     * @param bool $wrapResponse
     */
    public function __construct(
        protected TalerConfig $config,
        protected ?ClientInterface $client = null,
        protected bool $wrapResponse = true
    )
    {
        // $this->config = $config;
        // $this->wrapResponse = $wrapResponse;
        $this->httpClientWrapper = new HttpClientWrapper($config, $client);
    }

    public function getHttpClientWrapper(): HttpClientWrapper
    {
        return $this->httpClientWrapper;
    }

    public function getConfig(): TalerConfig
    {
        return $this->config;
    }

    public function getWrappedResponse(): bool
    {
        return $this->wrapResponse;
    }

    public function exchange(): ExchangeClient
    {
        return new ExchangeClient(
            $this,
            $this->httpClientWrapper
        );
    }
}

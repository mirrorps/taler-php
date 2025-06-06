<?php

namespace Taler;

use Psr\Http\Client\ClientInterface;
use Taler\Api\Exchange\ExchangeClient;
use Taler\Config\TalerConfig;
use Taler\Http\HttpClientWrapper;

class Taler
{
    protected HttpClientWrapper $httpClientWrapper;
    protected ExchangeClient $exchange;

    /**
     * @param TalerConfig $config
     * @param ClientInterface|null $client
     * @param bool $wrapResponse
     */
    public function __construct(
        protected TalerConfig $config,
        protected ?ClientInterface $client = null,
        protected bool $wrapResponse = true
    )
    {
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
        $this->exchange ??= new ExchangeClient(
            $this,
            $this->httpClientWrapper
        );

        return $this->exchange;
    }
}

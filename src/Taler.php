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
        TalerConfig $config,
        ?ClientInterface $client = null,
        bool $wrapResponse = true
    )
    {
        $this->httpClientWrapper = new HttpClientWrapper($config, $client);
    }

    public function getHttpClientWrapper(): HttpClientWrapper
    {
        return $this->httpClientWrapper;
    }

    public function exchange(): ExchangeClient
    {
        return new ExchangeClient($this->httpClientWrapper);
    }
}

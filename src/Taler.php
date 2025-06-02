<?php

namespace Taler;

use Psr\Http\Client\ClientInterface;
use Taler\Http\HttpClientWrapper;
use Taler\Config\TalerConfig;

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
        array $clientOptions = [],
        bool $wrapResponse = true
    )
    {
        $this->httpClientWrapper = new HttpClientWrapper($config, $client, $clientOptions, $wrapResponse);
    }

    public function getHttpClientWrapper(): HttpClientWrapper
    {
        return $this->httpClientWrapper;
    }
}

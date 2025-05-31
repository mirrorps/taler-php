<?php

namespace Taler;

use Psr\Http\Client\ClientInterface;
use Taler\Http\HttpClientWrapper;
use Taler\Config\TalerConfig;

class Taler
{

    public function __construct(
        private TalerConfig $config,
        private ?ClientInterface $client = null,
        private array $clientOptions = [],
        private bool $wrapResponse = true
    )
    {
        $this->httpClientWrapper = new HttpClientWrapper($config, $client, $clientOptions, $wrapResponse);
    }

}

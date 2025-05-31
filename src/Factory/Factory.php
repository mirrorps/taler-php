<?php

namespace Taler\Factory;

use Taler\Config\TalerConfig;
use Taler\Taler;

class Factory
{
    /**
     * @param array $options [
     * 'base_url'       => (string) Taler backend URL,
     * 'token'          => (string) Bearer token or empty,
     * 'client'         => (ClientInterface) Optional PSR-18 client,
     * 'clientOptions'  => (array) Optional custom headers,
     * 'logger'         => (LoggerInterface) Optional PSR-3 logger
     * ]
     */
    public static function create(array $options): Taler
    {
        $token  = $options['token']  ?? '';
        $client = $options['client'] ?? null;
        $clientOptions = $options['clientOptions'] ?? [];
        $wrapResponse  = $options['wrapResponse']  ?? true;

        $config = new TalerConfig(
            baseUrl: $options['base_url'],
            authToken: $token
        );

        return new Taler(
            $config,
            $client,
            $clientOptions,
            $wrapResponse,

        );
    }
}
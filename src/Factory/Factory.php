<?php

namespace Taler\Factory;

use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Taler\Config\TalerConfig;
use Taler\Taler;
use InvalidArgumentException;

class Factory
{
    /**
     * Create a new Taler instance
     * 
     * @param array{
     *     base_url: string,
     *     token?: string,
     *     client?: ClientInterface|null,
     *     logger?: LoggerInterface|null,
     *     cache?: CacheInterface|null,
     *     wrapResponse?: bool,
     *     debugLoggingEnabled?: bool
     * } $options Configuration options for creating Taler instance
     * @throws InvalidArgumentException when base_url is empty
     */
    public static function create(array $options): Taler
    {
        $token = $options['token'] ?? '';
        $client = $options['client'] ?? null;
        $logger = $options['logger'] ?? null;
        $cache = $options['cache'] ?? null;
        $wrapResponse = $options['wrapResponse'] ?? true;
        $debugLoggingEnabled = $options['debugLoggingEnabled'] ?? false;

        $config = new TalerConfig(
            baseUrl: $options['base_url'],
            authToken: $token,
            wrapResponse: $wrapResponse,
            debugLoggingEnabled: $debugLoggingEnabled
        );

        return new Taler(
            $config,
            $client,
            $logger,
            $cache
        );
    }
}
<?php

namespace Taler\Factory;

use Psr\Http\Client\ClientInterface;
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
     *     clientOptions?: array<string, string>,
     *     wrapResponse?: bool
     * } $options Configuration options for creating Taler instance
     * @throws InvalidArgumentException when base_url is empty
     */
    public static function create(array $options): Taler
    {
        $token = $options['token'] ?? '';
        $client = $options['client'] ?? null;
        $clientOptions = $options['clientOptions'] ?? [];
        $wrapResponse = $options['wrapResponse'] ?? true;

        $config = new TalerConfig(
            baseUrl: $options['base_url'],
            authToken: $token
        );

        return new Taler(
            $config,
            $client,
            $clientOptions,
            $wrapResponse
        );
    }
}
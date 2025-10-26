<?php

namespace Taler\Factory;

use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Taler\Api\Config\Dto\MerchantVersionResponse;
use Taler\Config\TalerConfig;
use Taler\Taler;
use InvalidArgumentException;
use function Taler\Helpers\parseLibtoolVersion;
use function Taler\Helpers\isProtocolCompatible;

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

        $taler = new Taler(
            $config,
            $client,
            $logger,
            $cache
        );

        // Early validation: ensure the configured backend is a merchant backend
        // by checking the name field from GET /config equals "taler-merchant".
        $configResponse = $taler->configApi()->getConfig();
        $name = $configResponse instanceof MerchantVersionResponse
            ? $configResponse->name
            : (string) ($configResponse['name'] ?? '');

        $name = strtolower($name);

        if ($name !== 'taler-merchant') {
            throw new InvalidArgumentException(
                sprintf('The configured backend is not a merchant backend (got name="%s").', $name)
            );
        }

        // Version compatibility warning (non-fatal): parse Taler versioning triplet and compare
        $version = $configResponse instanceof MerchantVersionResponse
            ? $configResponse->version
            : (string) ($configResponse['version'] ?? '');
        $parsed = $version !== '' ? parseLibtoolVersion($version) : null;
        if ($parsed !== null) {
            [$serverCurrent, , $serverAge] = $parsed;
            $clientCurrent = (int) Taler::TALER_PROTOCOL_VERSION;
            if (!isProtocolCompatible($serverCurrent, $serverAge, $clientCurrent)) {
                $taler->getLogger()->warning(
                    sprintf(
                        'Merchant backend protocol may be incompatible. Server version=%s (current=%d, age=%d), client current=%d',
                        $version,
                        $serverCurrent,
                        $serverAge,
                        $clientCurrent
                    )
                );
            }
        }

        return $taler;
    }
}
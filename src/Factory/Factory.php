<?php

namespace Taler\Factory;

use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Taler\Api\Config\Dto\MerchantVersionResponse;
use Taler\Api\Dto\RelativeTime;
use Taler\Api\Instance\Dto\LoginTokenRequest;
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
     *     // Optional: have Factory manage login to obtain an access token
     *     username?: string,
     *     password?: string,
     *     instance?: string, // instance ID to authenticate against
     *     scope?: "readonly"|"write"|"all"|"order-simple"|"order-pos"|"order-mgmt"|"order-full",
     *     duration_us?: int|string|null, // relative duration in microseconds or "forever"
     *     description?: string|null,
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
        $username = $options['username'] ?? null;
        $password = $options['password'] ?? null;
        $instanceId = $options['instance'] ?? null;
        $scope = $options['scope'] ?? null;
        $durationUs = $options['duration_us'] ?? null;
        $description = $options['description'] ?? null;
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

        // Configure optional token provider (Factory-managed auth)
        // Triggered when no token provided but credentials are supplied.
        $haveCreds = is_string($username) && $username !== '' && is_string($password) && $password !== '' && is_string($instanceId) && $instanceId !== '';
        if ($token === '' && $haveCreds) {
            // Default scope if not provided: readonly
            $desiredScope = is_string($scope) ? $scope : 'readonly';
            $relative = null;
            if ($durationUs !== null) {
                $relative = new RelativeTime($durationUs);
            }
            $loginRequest = new LoginTokenRequest(
                scope: $desiredScope,
                duration: $relative,
                description: $description,
                refreshable: null
            );

            $basic = 'Basic ' . base64_encode($username . ':' . $password);
            $provider = function () use (&$taler, $instanceId, $loginRequest, $basic): void {
                $response = $taler
                    ->instance()
                    ->getAccessToken(
                        $instanceId,
                        $loginRequest,
                        ['Authorization' => $basic]
                    );

                // Normalize DTO|array to array-like
                if ($response instanceof \Taler\Api\Instance\Dto\LoginTokenSuccessResponse) {
                    $accessToken = $response->access_token;
                    $expires = $response->expiration->t_s;
                } else {
                    $accessToken = (string) ($response['access_token'] ?? '');
                    $expires = $response['expiration']['t_s'] ?? null;
                }

                $taler->getConfig()->setAttribute('authToken', $accessToken);
                $expiresTs = is_int($expires) ? $expires : null;
                $taler->getConfig()->setAttribute('authTokenExpiresAtTs', $expiresTs);
            };

            // Install provider on config for automatic refresh
            $taler->getConfig()->setAttribute('tokenProvider', $provider);

            // Eagerly obtain the first token now so the instance is immediately usable
            $provider();
        }

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
<?php

namespace Taler\Api\Instance;

use Taler\Api\Base\AbstractApiClient;
use Taler\Api\Instance\Dto\InstanceConfigurationMessage;
use Taler\Api\Instance\Dto\InstanceAuthConfigToken;
use Taler\Api\Instance\Dto\InstanceAuthConfigTokenOLD;
use Taler\Api\Instance\Dto\InstanceAuthConfigExternal;
use Taler\Api\Instance\Dto\Challenge;
use Taler\Exception\TalerException;

/**
 * Client for managing merchant instances.
 */
class InstanceClient extends AbstractApiClient
{
    /**
     * Creates a new merchant instance.
     *
     * @param InstanceConfigurationMessage $instanceConfiguration The instance configuration data
     * @param array<string, string> $headers Optional request headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     */
    public function createInstance(InstanceConfigurationMessage $instanceConfiguration, array $headers = []): void
    {
        Actions\CreateInstance::run($this, $instanceConfiguration, $headers);
    }

    /**
     * Creates a new merchant instance asynchronously.
     *
     * @param InstanceConfigurationMessage $instanceConfiguration The instance configuration data
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function createInstanceAsync(InstanceConfigurationMessage $instanceConfiguration, array $headers = []): mixed
    {
        return Actions\CreateInstance::runAsync($this, $instanceConfiguration, $headers);
    }

    /**
     * Resets the password for a merchant instance.
     *
     * @param string $instanceId The instance ID
     * @param InstanceAuthConfigToken|InstanceAuthConfigTokenOLD|InstanceAuthConfigExternal $authConfig The new authentication configuration
     * @param array<string, string> $headers Optional request headers
     * @return Challenge|null Returns Challenge if 2FA is required, null on success
     * @throws TalerException
     * @throws \Throwable
     *
     * @since v21
     */
    public function forgotPassword(
        string $instanceId,
        InstanceAuthConfigToken|InstanceAuthConfigTokenOLD|InstanceAuthConfigExternal $authConfig,
        array $headers = []
    ): ?Challenge {
        return Actions\ForgotPassword::run($this, $instanceId, $authConfig, $headers);
    }

    /**
     * Resets the password for a merchant instance asynchronously.
     *
     * @param string $instanceId The instance ID
     * @param InstanceAuthConfigToken|InstanceAuthConfigTokenOLD|InstanceAuthConfigExternal $authConfig The new authentication configuration
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     *
     * @since v21
     */
    public function forgotPasswordAsync(
        string $instanceId,
        InstanceAuthConfigToken|InstanceAuthConfigTokenOLD|InstanceAuthConfigExternal $authConfig,
        array $headers = []
    ): mixed {
        return Actions\ForgotPassword::runAsync($this, $instanceId, $authConfig, $headers);
    }

    /**
     * Updates the authentication settings for a merchant instance.
     *
     * @param string $instanceId The instance ID
     * @param InstanceAuthConfigToken|InstanceAuthConfigTokenOLD|InstanceAuthConfigExternal $authConfig The authentication configuration
     * @param array<string, string> $headers Optional request headers
     * @return Challenge|null Returns Challenge if 2FA is required, null on success
     * @throws TalerException
     * @throws \Throwable
     *
     * @since v21
     */
    public function updateAuth(
        string $instanceId,
        InstanceAuthConfigToken|InstanceAuthConfigTokenOLD|InstanceAuthConfigExternal $authConfig,
        array $headers = []
    ): ?Challenge {
        return Actions\UpdateAuth::run($this, $instanceId, $authConfig, $headers);
    }

    /**
     * Updates the authentication settings for a merchant instance asynchronously.
     *
     * @param string $instanceId The instance ID
     * @param InstanceAuthConfigToken|InstanceAuthConfigTokenOLD|InstanceAuthConfigExternal $authConfig The authentication configuration
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     *
     * @since v21
     */
    public function updateAuthAsync(
        string $instanceId,
        InstanceAuthConfigToken|InstanceAuthConfigTokenOLD|InstanceAuthConfigExternal $authConfig,
        array $headers = []
    ): mixed {
        return Actions\UpdateAuth::runAsync($this, $instanceId, $authConfig, $headers);
    }
}

<?php

namespace Taler\Api\Instance;

use Taler\Api\Base\AbstractApiClient;
use Taler\Api\Instance\Dto\InstanceConfigurationMessage;
use Taler\Api\Instance\Dto\InstanceAuthConfigToken;
use Taler\Api\Instance\Dto\InstanceAuthConfigTokenOLD;
use Taler\Api\Instance\Dto\InstanceAuthConfigExternal;
use Taler\Api\Instance\Dto\Challenge;
use Taler\Api\Instance\Dto\LoginTokenRequest;
use Taler\Api\Instance\Dto\LoginTokenSuccessResponse;
use Taler\Api\Instance\Dto\GetAccessTokensRequest;
use Taler\Api\Instance\Dto\TokenInfos;
use Taler\Api\Instance\Dto\InstanceReconfigurationMessage;
use Taler\Api\Instance\Dto\QueryInstancesResponse;
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

    /**
     * Retrieve an access token for the merchant API for instance $INSTANCE.
     *
     * @param string $instanceId The instance ID
     * @param LoginTokenRequest $loginTokenRequest The login token request
     * @param array<string, string> $headers Optional request headers
     * @return LoginTokenSuccessResponse|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     *
     * @since v19
     */
    public function getAccessToken(
        string $instanceId,
        LoginTokenRequest $loginTokenRequest,
        array $headers = []
    ): LoginTokenSuccessResponse|array {
        return Actions\GetAccessToken::run($this, $instanceId, $loginTokenRequest, $headers);
    }

    /**
     * Retrieve an access token asynchronously.
     *
     * @param string $instanceId The instance ID
     * @param LoginTokenRequest $loginTokenRequest The login token request
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     *
     * @since v19
     */
    public function getAccessTokenAsync(
        string $instanceId,
        LoginTokenRequest $loginTokenRequest,
        array $headers = []
    ): mixed {
        return Actions\GetAccessToken::runAsync($this, $instanceId, $loginTokenRequest, $headers);
    }

    /**
     * Retrieve a list of issued access tokens.
     *
     * @param string $instanceId The instance ID
     * @param GetAccessTokensRequest|null $request Optional query parameters
     * @param array<string, string> $headers Optional request headers
     * @return TokenInfos|array<string, mixed>|null Null if 204 No Content
     * @throws TalerException
     * @throws \Throwable
     *
     * @since v19
     */
    public function getAccessTokens(
        string $instanceId,
        ?GetAccessTokensRequest $request = null,
        array $headers = []
    ): TokenInfos|array|null {
        return Actions\GetAccessTokens::run($this, $instanceId, $request, $headers);
    }

    /**
     * Retrieve a list of issued access tokens asynchronously.
     *
     * @param string $instanceId The instance ID
     * @param GetAccessTokensRequest|null $request Optional query parameters
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     *
     * @since v19
     */
    public function getAccessTokensAsync(
        string $instanceId,
        ?GetAccessTokensRequest $request = null,
        array $headers = []
    ): mixed {
        return Actions\GetAccessTokens::runAsync($this, $instanceId, $request, $headers);
    }

    /**
     * Delete the token presented in the authorization header for the instance.
     *
     * @param string $instanceId The instance ID
     * @param array<string, string> $headers Optional request headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     *
     * @since v19
     */
    public function deleteAccessToken(
        string $instanceId,
        array $headers = []
    ): void {
        Actions\DeleteAccessToken::run($this, $instanceId, $headers);
    }

    /**
     * Delete the token asynchronously.
     *
     * @param string $instanceId The instance ID
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     *
     * @since v19
     */
    public function deleteAccessTokenAsync(
        string $instanceId,
        array $headers = []
    ): mixed {
        return Actions\DeleteAccessToken::runAsync($this, $instanceId, $headers);
    }

    /**
     * Delete a token for $INSTANCE API access by its $SERIAL.
     *
     * @param string $instanceId The instance ID
     * @param int $serial The token serial to delete
     * @param array<string, string> $headers Optional request headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     *
     * @since v19
     */
    public function deleteAccessTokenBySerial(
        string $instanceId,
        int $serial,
        array $headers = []
    ): void {
        Actions\DeleteAccessTokenBySerial::run($this, $instanceId, $serial, $headers);
    }

    /**
     * Delete a token by serial asynchronously.
     *
     * @param string $instanceId The instance ID
     * @param int $serial The token serial to delete
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     *
     * @since v19
     */
    public function deleteAccessTokenBySerialAsync(
        string $instanceId,
        int $serial,
        array $headers = []
    ): mixed {
        return Actions\DeleteAccessTokenBySerial::runAsync($this, $instanceId, $serial, $headers);
    }

    /**
     * Update the configuration of a merchant instance.
     *
     * @param string $instanceId The instance ID
     * @param InstanceReconfigurationMessage $message The reconfiguration message
     * @param array<string, string> $headers Optional request headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     *
     * @since v19
     */
    public function updateInstance(
        string $instanceId,
        InstanceReconfigurationMessage $message,
        array $headers = []
    ): void {
        Actions\UpdateInstance::run($this, $instanceId, $message, $headers);
    }

    /**
     * Update the configuration of a merchant instance asynchronously.
     *
     * @param string $instanceId The instance ID
     * @param InstanceReconfigurationMessage $message The reconfiguration message
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     *
     * @since v19
     */
    public function updateInstanceAsync(
        string $instanceId,
        InstanceReconfigurationMessage $message,
        array $headers = []
    ): mixed {
        return Actions\UpdateInstance::runAsync($this, $instanceId, $message, $headers);
    }

    /**
     * Retrieve the list of all merchant instances (admin only).
     *
     * @param array<string, string> $headers Optional request headers
     * @return \Taler\Api\Instance\Dto\InstancesResponse|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     *
     * @since v19
     */
    public function getInstances(array $headers = []): \Taler\Api\Instance\Dto\InstancesResponse|array
    {
        return Actions\GetInstances::run($this, $headers);
    }

    /**
     * Retrieve the list of all merchant instances asynchronously.
     *
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     *
     * @since v19
     */
    public function getInstancesAsync(array $headers = []): mixed
    {
        return Actions\GetInstances::runAsync($this, $headers);
    }

    /**
     * Query a specific merchant instance.
     *
     * @param string $instanceId The instance ID
     * @param array<string, string> $headers Optional request headers
     * @return QueryInstancesResponse|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     *
     * @since v19
     */
    public function getInstance(string $instanceId, array $headers = []): QueryInstancesResponse|array
    {
        return Actions\GetInstance::run($this, $instanceId, $headers);
    }

    /**
     * Query a specific merchant instance asynchronously.
     *
     * @param string $instanceId The instance ID
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     *
     * @since v19
     */
    public function getInstanceAsync(string $instanceId, array $headers = []): mixed
    {
        return Actions\GetInstance::runAsync($this, $instanceId, $headers);
    }
}

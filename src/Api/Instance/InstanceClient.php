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
use Taler\Api\TwoFactorAuth\Dto\ChallengeResponse;

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
     * @return ChallengeResponse|null Returns ChallengeResponse if 2FA is required, null on success
     * @throws TalerException
     * @throws \Throwable
     *
     * @since v21
     */
    public function forgotPassword(
        string $instanceId,
        InstanceAuthConfigToken|InstanceAuthConfigTokenOLD|InstanceAuthConfigExternal $authConfig,
        array $headers = []
    ): ?ChallengeResponse {
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
     * @return LoginTokenSuccessResponse|\Taler\Api\TwoFactorAuth\Dto\ChallengeResponse|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     *
     * @since v19
     */
    public function getAccessToken(
        string $instanceId,
        LoginTokenRequest $loginTokenRequest,
        array $headers = []
    ): LoginTokenSuccessResponse|\Taler\Api\TwoFactorAuth\Dto\ChallengeResponse|array {
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
     * Check KYC status of a particular payment target.
     *
     * @param string $instanceId The instance ID
     * @param \Taler\Api\Instance\Dto\GetKycStatusRequest|null $request Optional query parameters
     * @param array<string, string> $headers Optional request headers
     * @return \Taler\Api\Instance\Dto\MerchantAccountKycRedirectsResponse|array<string, mixed>|null Null if 204 No Content
     * @throws TalerException
     * @throws \Throwable
     */
    public function getKycStatus(
        string $instanceId,
        ?\Taler\Api\Instance\Dto\GetKycStatusRequest $request = null,
        array $headers = []
    ): \Taler\Api\Instance\Dto\MerchantAccountKycRedirectsResponse|array|null {
        return Actions\GetKycStatus::run($this, $instanceId, $request, $headers);
    }

    /**
     * Check KYC status asynchronously.
     *
     * @param string $instanceId The instance ID
     * @param \Taler\Api\Instance\Dto\GetKycStatusRequest|null $request Optional query parameters
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function getKycStatusAsync(
        string $instanceId,
        ?\Taler\Api\Instance\Dto\GetKycStatusRequest $request = null,
        array $headers = []
    ): mixed {
        return Actions\GetKycStatus::runAsync($this, $instanceId, $request, $headers);
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

    /**
     * Retrieve merchant statistics where values are amounts for the given $SLUG.
     *
     * @param string $instanceId The instance ID
     * @param string $slug The statistics slug
     * @param \Taler\Api\Instance\Dto\GetMerchantStatisticsAmountRequest|null $request Optional query parameters
     * @param array<string, string> $headers Optional request headers
     * @return \Taler\Api\Instance\Dto\MerchantStatisticsAmountResponse|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public function getMerchantStatisticsAmount(
        string $instanceId,
        string $slug,
        ?\Taler\Api\Instance\Dto\GetMerchantStatisticsAmountRequest $request = null,
        array $headers = []
    ): \Taler\Api\Instance\Dto\MerchantStatisticsAmountResponse|array {
        return Actions\GetMerchantStatisticsAmount::run($this, $instanceId, $slug, $request, $headers);
    }

    /**
     * Retrieve merchant statistics where values are amounts asynchronously.
     *
     * @param string $instanceId The instance ID
     * @param string $slug The statistics slug
     * @param \Taler\Api\Instance\Dto\GetMerchantStatisticsAmountRequest|null $request Optional query parameters
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function getMerchantStatisticsAmountAsync(
        string $instanceId,
        string $slug,
        ?\Taler\Api\Instance\Dto\GetMerchantStatisticsAmountRequest $request = null,
        array $headers = []
    ): mixed {
        return Actions\GetMerchantStatisticsAmount::runAsync($this, $instanceId, $slug, $request, $headers);
    }

    /**
     * Retrieve merchant statistics where values are counters for the given $SLUG.
     *
     * @param string $instanceId The instance ID
     * @param string $slug The statistics slug
     * @param \Taler\Api\Instance\Dto\GetMerchantStatisticsCounterRequest|null $request Optional query parameters
     * @param array<string, string> $headers Optional request headers
     * @return \Taler\Api\Instance\Dto\MerchantStatisticsCounterResponse|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public function getMerchantStatisticsCounter(
        string $instanceId,
        string $slug,
        ?\Taler\Api\Instance\Dto\GetMerchantStatisticsCounterRequest $request = null,
        array $headers = []
    ): \Taler\Api\Instance\Dto\MerchantStatisticsCounterResponse|array {
        return Actions\GetMerchantStatisticsCounter::run($this, $instanceId, $slug, $request, $headers);
    }

    /**
     * Retrieve merchant statistics where values are counters asynchronously.
     *
     * @param string $instanceId The instance ID
     * @param string $slug The statistics slug
     * @param \Taler\Api\Instance\Dto\GetMerchantStatisticsCounterRequest|null $request Optional query parameters
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function getMerchantStatisticsCounterAsync(
        string $instanceId,
        string $slug,
        ?\Taler\Api\Instance\Dto\GetMerchantStatisticsCounterRequest $request = null,
        array $headers = []
    ): mixed {
        return Actions\GetMerchantStatisticsCounter::runAsync($this, $instanceId, $slug, $request, $headers);
    }

    /**
     * Delete (disable) or purge a merchant instance.
     *
     * @param string $instanceId The instance ID
     * @param bool $purge If true, include purge=YES
     * @param array<string, string> $headers Optional request headers
     * @return ChallengeResponse|null Returns ChallengeResponse if 2FA is required (202), null on success (204)
     * @throws TalerException
     * @throws \Throwable
     */
    public function deleteInstance(
        string $instanceId,
        bool $purge = false,
        array $headers = []
    ): ?ChallengeResponse {
        return Actions\DeleteInstance::run($this, $instanceId, $purge, $headers);
    }

    /**
     * Delete (disable) or purge a merchant instance asynchronously.
     *
     * @param string $instanceId The instance ID
     * @param bool $purge If true, include purge=YES
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function deleteInstanceAsync(
        string $instanceId,
        bool $purge = false,
        array $headers = []
    ): mixed {
        return Actions\DeleteInstance::runAsync($this, $instanceId, $purge, $headers);
    }
}

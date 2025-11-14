<?php

namespace Taler\Api\TwoFactorAuth;

use Taler\Api\Base\AbstractApiClient;
use Taler\Exception\TalerException;
use Taler\Api\TwoFactorAuth\Actions\RequestChallenge;
use Taler\Api\TwoFactorAuth\Actions\ConfirmChallenge;
use Taler\Api\TwoFactorAuth\Dto\MerchantChallengeSolveRequest;

class TwoFactorAuthClient extends AbstractApiClient
{
    /**
     * Request TAN code transmission for a given challenge.
     *
     * POST /instances/$INSTANCE/challenge/$CHALLENGE_ID
     *
     * @param string $instanceId The instance ID
     * @param string $challengeId The challenge ID
     * @param array<string, mixed>|null $requestBody Optional JSON-object body (defaults to {})
     * @param array<string, string> $headers Optional request headers
     * @return \Taler\Api\TwoFactorAuth\Dto\ChallengeRequestResponse
     * @throws TalerException
     * @throws \Throwable
     *
     * @since v21
     */
    public function requestChallenge(
        string $instanceId,
        string $challengeId,
        ?array $requestBody = null,
        array $headers = []
    ): \Taler\Api\TwoFactorAuth\Dto\ChallengeRequestResponse {
        return RequestChallenge::run($this, $instanceId, $challengeId, $requestBody, $headers);
    }

    /**
     * Request TAN code transmission for a given challenge asynchronously.
     *
     * POST /instances/$INSTANCE/challenge/$CHALLENGE_ID
     *
     * @param string $instanceId The instance ID
     * @param string $challengeId The challenge ID
     * @param array<string, mixed>|null $requestBody Optional JSON-object body (defaults to {})
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     *
     * @since v21
     */
    public function requestChallengeAsync(
        string $instanceId,
        string $challengeId,
        ?array $requestBody = null,
        array $headers = []
    ): mixed {
        return RequestChallenge::runAsync($this, $instanceId, $challengeId, $requestBody, $headers);
    }

    /**
     * Confirm a TAN challenge with the provided TAN.
     *
     * POST /instances/$INSTANCE/challenge/$CHALLENGE_ID/confirm
     *
     * @param string $instanceId The instance ID
     * @param string $challengeId The challenge ID
     * @param MerchantChallengeSolveRequest $requestBody The request body containing the TAN
     * @param array<string, string> $headers Optional request headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     *
     * @since v21
     */
    public function confirmChallenge(
        string $instanceId,
        string $challengeId,
        MerchantChallengeSolveRequest $requestBody,
        array $headers = []
    ): void {
        ConfirmChallenge::run($this, $instanceId, $challengeId, $requestBody, $headers);
    }

    /**
     * Confirm a TAN challenge asynchronously.
     *
     * POST /instances/$INSTANCE/challenge/$CHALLENGE_ID/confirm
     *
     * @param string $instanceId The instance ID
     * @param string $challengeId The challenge ID
     * @param MerchantChallengeSolveRequest $requestBody The request body containing the TAN
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     *
     * @since v21
     */
    public function confirmChallengeAsync(
        string $instanceId,
        string $challengeId,
        MerchantChallengeSolveRequest $requestBody,
        array $headers = []
    ): mixed {
        return ConfirmChallenge::runAsync($this, $instanceId, $challengeId, $requestBody, $headers);
    }
}



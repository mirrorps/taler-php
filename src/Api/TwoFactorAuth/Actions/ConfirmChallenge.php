<?php

namespace Taler\Api\TwoFactorAuth\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\TwoFactorAuth\Dto\MerchantChallengeSolveRequest;
use Taler\Api\TwoFactorAuth\TwoFactorAuthClient;
use Taler\Exception\TalerException;

/**
 * Solve a TAN challenge by confirming it with the TAN code.
 *
 * POST /instances/$INSTANCE/challenge/$CHALLENGE_ID/confirm
 *
 * @since v21
 */
class ConfirmChallenge
{
    public function __construct(
        private TwoFactorAuthClient $twoFactorAuthClient
    ) {
    }

    /**
     * Confirm the given challenge.
     *
     * @param TwoFactorAuthClient $twoFactorAuthClient
     * @param string $instanceId
     * @param string $challengeId
     * @param MerchantChallengeSolveRequest $requestBody
     * @param array<string, string> $headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(
        TwoFactorAuthClient $twoFactorAuthClient,
        string $instanceId,
        string $challengeId,
        MerchantChallengeSolveRequest $requestBody,
        array $headers = []
    ): void {
        $self = new self($twoFactorAuthClient);

        try {
            $body = json_encode($requestBody, JSON_THROW_ON_ERROR);

            $self->twoFactorAuthClient->setResponse(
                $self->twoFactorAuthClient->getClient()->request(
                    'POST',
                    "instances/{$instanceId}/challenge/{$challengeId}/confirm",
                    $headers,
                    $body
                )
            );

            $self->handleResponse($self->twoFactorAuthClient->getResponse());
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $sanitized = \Taler\Helpers\sanitizeString((string) $e->getMessage());
            $twoFactorAuthClient->getTaler()->getLogger()->error("Taler confirm challenge failed: {$e->getCode()}, {$sanitized}");
            throw $e;
        }
    }

    /**
     * Confirm the given challenge asynchronously.
     *
     * @param TwoFactorAuthClient $twoFactorAuthClient
     * @param string $instanceId
     * @param string $challengeId
     * @param MerchantChallengeSolveRequest $requestBody
     * @param array<string, string> $headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public static function runAsync(
        TwoFactorAuthClient $twoFactorAuthClient,
        string $instanceId,
        string $challengeId,
        MerchantChallengeSolveRequest $requestBody,
        array $headers = []
    ): mixed {
        $self = new self($twoFactorAuthClient);

        $body = json_encode($requestBody, JSON_THROW_ON_ERROR);

        return $twoFactorAuthClient
            ->getClient()
            ->requestAsync(
                'POST',
                "instances/{$instanceId}/challenge/{$challengeId}/confirm",
                $headers,
                $body
            )
            ->then(function (ResponseInterface $response) use ($self) {
                $self->twoFactorAuthClient->setResponse($response);
                $self->handleResponse($response);
                return null;
            });
    }

    /**
     * @throws TalerException
     */
    private function handleResponse(ResponseInterface $response): void
    {
        $this->twoFactorAuthClient->parseResponseBody($response, 204);
    }
}



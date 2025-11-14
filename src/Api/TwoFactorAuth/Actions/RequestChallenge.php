<?php

namespace Taler\Api\TwoFactorAuth\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Base\AbstractApiClient;
use Taler\Api\TwoFactorAuth\Dto\ChallengeRequestResponse;
use Taler\Api\TwoFactorAuth\TwoFactorAuthClient;
use Taler\Exception\TalerException;

/**
 * Request TAN code for a given challenge.
 *
 * POST /instances/$INSTANCE/challenge/$CHALLENGE_ID
 *
 * This endpoint may be used to trigger a retransmission of the TAN or request it again
 * if the current code has expired or too many attempts were made.
 *
 * @since v21
 */
class RequestChallenge
{
    public function __construct(
        private TwoFactorAuthClient $twoFactorAuthClient
    ) {
    }

    /**
     * Send TAN request for the given challenge.
     *
     * @param TwoFactorAuthClient $twoFactorAuthClient
     * @param string $instanceId
     * @param string $challengeId
     * @param array<string, mixed>|null $requestBody Optional JSON-object body (defaults to {})
     * @param array<string, string> $headers
     * @return ChallengeRequestResponse
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(
        TwoFactorAuthClient $twoFactorAuthClient,
        string $instanceId,
        string $challengeId,
        ?array $requestBody = null,
        array $headers = []
    ): ChallengeRequestResponse {
        $self = new self($twoFactorAuthClient);

        try {
            // Body must be a JSON object, can be empty. Cast to object to force {} for empty arrays.
            $body = json_encode((object) ($requestBody ?? []), JSON_THROW_ON_ERROR);

            $self->twoFactorAuthClient->setResponse(
                $self->twoFactorAuthClient->getClient()->request(
                    'POST',
                    "instances/{$instanceId}/challenge/{$challengeId}",
                    $headers,
                    $body
                )
            );

            return $self->handleResponse($self->twoFactorAuthClient->getResponse());
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $sanitized = \Taler\Helpers\sanitizeString((string) $e->getMessage());
            $twoFactorAuthClient->getTaler()->getLogger()->error("Taler request challenge failed: {$e->getCode()}, {$sanitized}");
            throw $e;
        }
    }

    /**
     * Send TAN request for the given challenge asynchronously.
     *
     * @param TwoFactorAuthClient $twoFactorAuthClient
     * @param string $instanceId
     * @param string $challengeId
     * @param array<string, mixed>|null $requestBody Optional JSON-object body (defaults to {})
     * @param array<string, string> $headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public static function runAsync(
        TwoFactorAuthClient $twoFactorAuthClient,
        string $instanceId,
        string $challengeId,
        ?array $requestBody = null,
        array $headers = []
    ): mixed {
        $self = new self($twoFactorAuthClient);

        // Body must be a JSON object, can be empty. Cast to object to force {} for empty arrays.
        $body = json_encode((object) ($requestBody ?? []), JSON_THROW_ON_ERROR);

        return $twoFactorAuthClient
            ->getClient()
            ->requestAsync(
                'POST',
                "instances/{$instanceId}/challenge/{$challengeId}",
                $headers,
                $body
            )
            ->then(function (ResponseInterface $response) use ($self) {
                $self->twoFactorAuthClient->setResponse($response);
                return $self->handleResponse($response);
            });
    }

    /**
     * @throws TalerException
     */
    private function handleResponse(ResponseInterface $response): ChallengeRequestResponse
    {
        $data = $this->twoFactorAuthClient->parseResponseBody($response, 200);
        return ChallengeRequestResponse::createFromArray($data);
    }
}



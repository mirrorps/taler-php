<?php

namespace Taler\Api\DonauCharity\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\DonauCharity\DonauCharityClient;
use Taler\Api\DonauCharity\Dto\PostDonauRequest;
use Taler\Api\TwoFactorAuth\Dto\ChallengeResponse;
use Taler\Exception\TalerException;

use const Taler\Http\HTTP_STATUS_CODE_ACCEPTED;
use const Taler\Http\HTTP_STATUS_CODE_NO_CONTENT;

/**
 * Action for linking a new Donau charity instance to the current instance context.
 *
 * Endpoint: POST private/donau
 * Required permission: donau-write
 */
class CreateDonauCharity
{
    public function __construct(
        private DonauCharityClient $client
    ) {}

    /**
     * Link a new Donau charity instance.
     *
     * @param DonauCharityClient $client
     * @param PostDonauRequest $request
     * @param array<string, string> $headers
     * @return ChallengeResponse|null Returns ChallengeResponse if 2FA is required (202), null on success (204)
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(
        DonauCharityClient $client,
        PostDonauRequest $request,
        array $headers = []
    ): ?ChallengeResponse {
        $self = new self($client);

        try {
            $body = json_encode($request, JSON_THROW_ON_ERROR);

            $self->client->setResponse(
                $self->client->getClient()->request(
                    'POST',
                    'private/donau',
                    $headers,
                    $body
                )
            );

            return $self->handleResponse($self->client->getResponse());
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $sanitized = \Taler\Helpers\sanitizeString((string) $e->getMessage());
            $client->getTaler()->getLogger()->error("Taler create Donau charity request failed: {$e->getCode()}, {$sanitized}");
            throw $e;
        }
    }

    /**
     * Async variant
     *
     * @param DonauCharityClient $client
     * @param PostDonauRequest $request
     * @param array<string, string> $headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public static function runAsync(
        DonauCharityClient $client,
        PostDonauRequest $request,
        array $headers = []
    ): mixed {
        $self = new self($client);

        $body = json_encode($request, JSON_THROW_ON_ERROR);

        return $client
            ->getClient()
            ->requestAsync(
                'POST',
                'private/donau',
                $headers,
                $body
            )
            ->then(function (ResponseInterface $response) use ($self) {
                $self->client->setResponse($response);
                return $self->handleResponse($response);
            });
    }

    /**
     * @param ResponseInterface $response
     * @return ChallengeResponse|null
     * @throws TalerException
     */
    private function handleResponse(ResponseInterface $response): ?ChallengeResponse
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode === HTTP_STATUS_CODE_ACCEPTED) {
            /** @var array{
             *   challenges: array<int, array{challenge_id: string, tan_channel: string, tan_info: string}>,
             *   combi_and: bool
             * } $data
             */
            $data = $this->client->parseResponseBody($response, HTTP_STATUS_CODE_ACCEPTED);
            return ChallengeResponse::createFromArray($data);
        }

        $this->client->parseResponseBody($response, HTTP_STATUS_CODE_NO_CONTENT);

        return null;
    }
}




<?php

namespace Taler\Api\DonauCharity\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\DonauCharity\DonauCharityClient;
use Taler\Api\DonauCharity\Dto\PostDonauRequest;
use Taler\Api\Instance\Dto\Challenge;
use Taler\Exception\TalerException;

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
     * @return Challenge|null Returns Challenge if 2FA is required (202), null on success (204)
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(
        DonauCharityClient $client,
        PostDonauRequest $request,
        array $headers = []
    ): ?Challenge {
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
     * @return Challenge|null
     * @throws TalerException
     */
    private function handleResponse(ResponseInterface $response): ?Challenge
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode === 202) {
            /** @var array{challenge_id: string} $data */
            $data = $this->client->parseResponseBody($response, 202);
            return Challenge::createFromArray($data);
        }

        $this->client->parseResponseBody($response, 204);

        return null;
    }
}




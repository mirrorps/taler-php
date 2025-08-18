<?php

namespace Taler\Api\TokenFamilies\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\TokenFamilies\Dto\TokenFamilyCreateRequest;
use Taler\Api\TokenFamilies\TokenFamiliesClient;
use Taler\Exception\TalerException;

class CreateTokenFamily
{
    public function __construct(
        private TokenFamiliesClient $client
    ) {}

    /**
     * Creates a new token family.
     *
     * @param TokenFamiliesClient $client
     * @param TokenFamilyCreateRequest $details
     * @param array<string, string> $headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(
        TokenFamiliesClient $client,
        TokenFamilyCreateRequest $details,
        array $headers = []
    ): void {
        $self = new self($client);

        try {
            $body = json_encode($details, JSON_THROW_ON_ERROR);

            $self->client->setResponse(
                $self->client->getClient()->request(
                    'POST',
                    'private/tokenfamilies',
                    $headers,
                    $body
                )
            );

            $client->handleWrappedResponse($self->handleResponse(...));
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $client->getTaler()->getLogger()->error("Taler create token family request failed: {$e->getCode()}, {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Creates a new token family asynchronously.
     *
     * @param TokenFamiliesClient $client
     * @param TokenFamilyCreateRequest $details
     * @param array<string, string> $headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public static function runAsync(
        TokenFamiliesClient $client,
        TokenFamilyCreateRequest $details,
        array $headers = []
    ): mixed {
        $self = new self($client);

        $body = json_encode($details, JSON_THROW_ON_ERROR);

        return $client
            ->getClient()
            ->requestAsync('POST', 'private/tokenfamilies', $headers, $body)
            ->then(function (ResponseInterface $response) use ($self) {
                $self->client->setResponse($response);
                return $self->client->handleWrappedResponse($self->handleResponse(...));
            });
    }

    private function handleResponse(ResponseInterface $response): void
    {
        // Endpoint returns 204 No Content
        $this->client->parseResponseBody($response, 204);
    }
}



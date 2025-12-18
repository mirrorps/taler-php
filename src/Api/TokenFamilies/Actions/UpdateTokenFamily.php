<?php

namespace Taler\Api\TokenFamilies\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\TokenFamilies\Dto\TokenFamilyDetails;
use Taler\Api\TokenFamilies\Dto\TokenFamilyUpdateRequest;
use Taler\Api\TokenFamilies\TokenFamiliesClient;
use Taler\Exception\TalerException;

class UpdateTokenFamily
{
    public function __construct(
        private TokenFamiliesClient $client
    ) {}

    /**
     * Updates an existing token family.
     *
     * @param TokenFamiliesClient $client
     * @param string $slug
     * @param TokenFamilyUpdateRequest $details
     * @param array<string, string> $headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(
        TokenFamiliesClient $client,
        string $slug,
        TokenFamilyUpdateRequest $details,
        array $headers = []
    ): void {
        $self = new self($client);

        try {
            $body = json_encode($details, JSON_THROW_ON_ERROR);

            $self->client->setResponse(
                $self->client->getClient()->request(
                    'PATCH',
                    "private/tokenfamilies/{$slug}",
                    $headers,
                    $body
                )
            );

            $client->handleWrappedResponse($self->handleResponse(...));
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $sanitized = \Taler\Helpers\sanitizeString((string) $e->getMessage());
            $client->getTaler()->getLogger()->error("Taler update token family request failed: {$e->getCode()}, {$sanitized}");
            throw $e;
        }
    }

    /**
     * Updates an existing token family asynchronously.
     *
     * @param TokenFamiliesClient $client
     * @param string $slug
     * @param TokenFamilyUpdateRequest $details
     * @param array<string, string> $headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public static function runAsync(
        TokenFamiliesClient $client,
        string $slug,
        TokenFamilyUpdateRequest $details,
        array $headers = []
    ): mixed {
        $self = new self($client);

        $body = json_encode($details, JSON_THROW_ON_ERROR);

        return $client
            ->getClient()
            ->requestAsync('PATCH', "private/tokenfamilies/{$slug}", $headers, $body)
            ->then(function (ResponseInterface $response) use ($self) {
                $self->client->setResponse($response);
                return $self->client->handleWrappedResponse($self->handleResponse(...));
            });
    }

    /**
     * Handles the response from the update token family request.
     */
    private function handleResponse(ResponseInterface $response): void
    {
        $this->client->parseResponseBody($response, 204);
    }
}



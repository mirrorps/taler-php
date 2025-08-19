<?php

namespace Taler\Api\TokenFamilies\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\TokenFamilies\TokenFamiliesClient;
use Taler\Exception\TalerException;

class DeleteTokenFamily
{
    public function __construct(
        private TokenFamiliesClient $client
    ) {}

    /**
     * Deletes a token family.
     *
     * @param TokenFamiliesClient $client
     * @param string $slug
     * @param array<string, string> $headers Optional request headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(TokenFamiliesClient $client, string $slug, array $headers = []): void
    {
        $self = new self($client);

        try {
            $self->client->setResponse(
                $self->client->getClient()->request('DELETE', "private/tokenfamilies/{$slug}", $headers)
            );

            $client->handleWrappedResponse($self->handleResponse(...));
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $client->getTaler()->getLogger()->error("Taler delete token family request failed: {$e->getCode()}, {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Deletes a token family asynchronously.
     *
     * @param TokenFamiliesClient $client
     * @param string $slug
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public static function runAsync(TokenFamiliesClient $client, string $slug, array $headers = []): mixed
    {
        $self = new self($client);

        return $client
            ->getClient()
            ->requestAsync('DELETE', "private/tokenfamilies/{$slug}", $headers)
            ->then(function (ResponseInterface $response) use ($self) {
                $self->client->setResponse($response);
                return $self->client->handleWrappedResponse($self->handleResponse(...));
            });
    }

    private function handleResponse(ResponseInterface $response): void
    {
        $this->client->parseResponseBody($response, 204);
    }
}



<?php

namespace Taler\Api\TokenFamilies\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\TokenFamilies\Dto\TokenFamiliesList;
use Taler\Api\TokenFamilies\TokenFamiliesClient;
use Taler\Exception\TalerException;

class GetTokenFamilies
{
    public function __construct(
        private TokenFamiliesClient $client
    ) {}

    /**
     * Get all token families for the merchant instance.
     *
     * @param TokenFamiliesClient $client
     * @param array<string, string> $headers Optional request headers
     * @return TokenFamiliesList|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(TokenFamiliesClient $client, array $headers = []): TokenFamiliesList|array
    {
        $self = new self($client);

        try {
            $self->client->setResponse(
                $self->client->getClient()->request('GET', 'private/tokenfamilies', $headers)
            );

            /** @var TokenFamiliesList|array{token_families: array<int, array<string, mixed>>} $result */
            $result = $client->handleWrappedResponse($self->handleResponse(...));
            return $result;
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $client->getTaler()->getLogger()->error("Taler get token families request failed: {$e->getCode()}, {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Async variant of get token families.
     *
     * @param TokenFamiliesClient $client
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     */
    public static function runAsync(TokenFamiliesClient $client, array $headers = []): mixed
    {
        $self = new self($client);

        return $client
            ->getClient()
            ->requestAsync('GET', 'private/tokenfamilies', $headers)
            ->then(function (ResponseInterface $response) use ($self) {
                $self->client->setResponse($response);
                return $self->client->handleWrappedResponse($self->handleResponse(...));
            });
    }

    private function handleResponse(ResponseInterface $response): TokenFamiliesList
    {
        $data = $this->client->parseResponseBody($response, 200);
        return TokenFamiliesList::createFromArray($data);
    }
}



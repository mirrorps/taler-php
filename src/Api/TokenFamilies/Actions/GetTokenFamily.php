<?php

namespace Taler\Api\TokenFamilies\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\TokenFamilies\Dto\TokenFamilyDetails;
use Taler\Api\TokenFamilies\TokenFamiliesClient;
use Taler\Exception\TalerException;

class GetTokenFamily
{
    public function __construct(
        private TokenFamiliesClient $client
    ) {}

    /**
     * Get a token family by slug.
     *
     * @param TokenFamiliesClient $client
     * @param string $slug
     * @param array<string, string> $headers Optional request headers
     * @return TokenFamilyDetails|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(TokenFamiliesClient $client, string $slug, array $headers = []): TokenFamilyDetails|array
    {
        $self = new self($client);

        try {
            $self->client->setResponse(
                $self->client->getClient()->request('GET', "private/tokenfamilies/{$slug}", $headers)
            );

            /** @var TokenFamilyDetails|array<string,mixed> $result */
            $result = $client->handleWrappedResponse($self->handleResponse(...));
            return $result;
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $sanitized = \Taler\Helpers\sanitizeString((string) $e->getMessage());
            $client->getTaler()->getLogger()->error("Taler get token family request failed: {$e->getCode()}, {$sanitized}");
            throw $e;
        }
    }

    /**
     * Async get token family.
     *
     * @param TokenFamiliesClient $client
     * @param string $slug
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     */
    public static function runAsync(TokenFamiliesClient $client, string $slug, array $headers = []): mixed
    {
        $self = new self($client);

        return $client
            ->getClient()
            ->requestAsync('GET', "private/tokenfamilies/{$slug}", $headers)
            ->then(function (ResponseInterface $response) use ($self) {
                $self->client->setResponse($response);
                return $self->client->handleWrappedResponse($self->handleResponse(...));
            });
    }

    private function handleResponse(ResponseInterface $response): TokenFamilyDetails
    {
        $data = $this->client->parseResponseBody($response, 200);
        return TokenFamilyDetails::createFromArray($data);
    }
}



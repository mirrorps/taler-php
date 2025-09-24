<?php

namespace Taler\Api\Inventory\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Inventory\Dto\ProductAddDetail;
use Taler\Api\Inventory\InventoryClient;
use Taler\Exception\TalerException;

class CreateProduct
{
    public function __construct(
        private InventoryClient $client
    ) {}

    /**
     * @param InventoryClient $client
     * @param ProductAddDetail $details
     * @param array<string, string> $headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(InventoryClient $client, ProductAddDetail $details, array $headers = []): void
    {
        $self = new self($client);

        try {
            $body = json_encode($details, JSON_THROW_ON_ERROR);

            $self->client->setResponse(
                $self->client->getClient()->request(
                    'POST',
                    'private/products',
                    $headers,
                    $body
                )
            );

            $client->handleWrappedResponse($self->handleResponse(...));
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $sanitized = \Taler\Helpers\sanitizeString((string) $e->getMessage());
            $client->getTaler()->getLogger()->error("Taler create product request failed: {$e->getCode()}, {$sanitized}");
            throw $e;
        }
    }

    /**
     * @param InventoryClient $client
     * @param ProductAddDetail $details
     * @param array<string, string> $headers
     * @return mixed
     */
    public static function runAsync(InventoryClient $client, ProductAddDetail $details, array $headers = []): mixed
    {
        $self = new self($client);

        $body = json_encode($details, JSON_THROW_ON_ERROR);

        return $client
            ->getClient()
            ->requestAsync('POST', 'private/products', $headers, $body)
            ->then(function (ResponseInterface $response) use ($self) {
                $self->client->setResponse($response);
                return $self->client->handleWrappedResponse($self->handleResponse(...));
            });
    }

    private function handleResponse(ResponseInterface $response): void
    {
        // Endpoint returns 204 No Content; parse will validate status code
        $this->client->parseResponseBody($response, 204);
    }
}



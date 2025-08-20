<?php

namespace Taler\Api\Inventory\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Inventory\Dto\ProductPatchDetail;
use Taler\Api\Inventory\InventoryClient;
use Taler\Exception\TalerException;

class UpdateProduct
{
    public function __construct(
        private InventoryClient $client
    ) {}

    /**
     * @param InventoryClient $client
     * @param string $productId
     * @param ProductPatchDetail $details
     * @param array<string, string> $headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(InventoryClient $client, string $productId, ProductPatchDetail $details, array $headers = []): void
    {
        $self = new self($client);

        try {
            $body = json_encode($details, JSON_THROW_ON_ERROR);

            $self->client->setResponse(
                $self->client->getClient()->request(
                    'PATCH',
                    "private/products/{$productId}",
                    $headers,
                    $body
                )
            );

            $client->handleWrappedResponse($self->handleResponse(...));
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $client->getTaler()->getLogger()->error("Taler update product request failed: {$e->getCode()}, {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * @param InventoryClient $client
     * @param string $productId
     * @param ProductPatchDetail $details
     * @param array<string, string> $headers
     * @return mixed
     */
    public static function runAsync(InventoryClient $client, string $productId, ProductPatchDetail $details, array $headers = []): mixed
    {
        $self = new self($client);

        $body = json_encode($details, JSON_THROW_ON_ERROR);

        return $client
            ->getClient()
            ->requestAsync('PATCH', "private/products/{$productId}", $headers, $body)
            ->then(function (ResponseInterface $response) use ($self) {
                $self->client->setResponse($response);
                return $self->client->handleWrappedResponse($self->handleResponse(...));
            });
    }

    private function handleResponse(ResponseInterface $response): void
    {
        // Per docs, 204 No Content indicates success; 404 and 409 are error statuses
        $this->client->parseResponseBody($response, 204);
    }
}



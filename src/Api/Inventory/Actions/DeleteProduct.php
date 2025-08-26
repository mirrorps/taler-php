<?php

namespace Taler\Api\Inventory\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Inventory\InventoryClient;
use Taler\Exception\TalerException;

class DeleteProduct
{
    public function __construct(
        private InventoryClient $client
    ) {}

    /**
     * @param InventoryClient $client
     * @param string $productId
     * @param array<string, string> $headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(InventoryClient $client, string $productId, array $headers = []): void
    {
        $self = new self($client);

        try {
            $self->client->setResponse(
                $self->client->getClient()->request(
                    'DELETE',
                    "private/products/{$productId}",
                    $headers
                )
            );

            $client->handleWrappedResponse($self->handleResponse(...));
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $client->getTaler()->getLogger()->error("Taler delete product request failed: {$e->getCode()}, {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * @param InventoryClient $client
     * @param string $productId
     * @param array<string, string> $headers
     * @return mixed
     */
    public static function runAsync(InventoryClient $client, string $productId, array $headers = []): mixed
    {
        $self = new self($client);

        return $client
            ->getClient()
            ->requestAsync('DELETE', "private/products/{$productId}", $headers)
            ->then(function (ResponseInterface $response) use ($self) {
                $self->client->setResponse($response);
                return $self->client->handleWrappedResponse($self->handleResponse(...));
            });
    }

    private function handleResponse(ResponseInterface $response): void
    {
        // for other statuses (e.g., 409), throw
        $this->client->parseResponseBody($response, 204);
    }
}



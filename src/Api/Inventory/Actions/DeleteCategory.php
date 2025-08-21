<?php

namespace Taler\Api\Inventory\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Inventory\InventoryClient;
use Taler\Exception\TalerException;

class DeleteCategory
{
    public function __construct(
        private InventoryClient $client
    ) {}

    /**
     * @param InventoryClient $client
     * @param int $categoryId
     * @param array<string, string> $headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(InventoryClient $client, int $categoryId, array $headers = []): void
    {
        $self = new self($client);

        try {
            $self->client->setResponse(
                $self->client->getClient()->request(
                    'DELETE',
                    "private/categories/{$categoryId}",
                    $headers
                )
            );

            $client->handleWrappedResponse($self->handleResponse(...));
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $client->getTaler()->getLogger()->error("Taler delete category request failed: {$e->getCode()}, {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * @param InventoryClient $client
     * @param int $categoryId
     * @param array<string, string> $headers
     * @return mixed
     */
    public static function runAsync(InventoryClient $client, int $categoryId, array $headers = []): mixed
    {
        $self = new self($client);

        return $client
            ->getClient()
            ->requestAsync('DELETE', "private/categories/{$categoryId}", $headers)
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



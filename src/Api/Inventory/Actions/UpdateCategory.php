<?php

namespace Taler\Api\Inventory\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Inventory\Dto\CategoryCreateRequest;
use Taler\Api\Inventory\InventoryClient;
use Taler\Exception\TalerException;

class UpdateCategory
{
    public function __construct(
        private InventoryClient $client
    ) {}

    /**
     * @param InventoryClient $client
     * @param int $categoryId
     * @param CategoryCreateRequest $request
     * @param array<string, string> $headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(InventoryClient $client, int $categoryId, CategoryCreateRequest $request, array $headers = []): void
    {
        $self = new self($client);

        try {
            $body = json_encode($request, JSON_THROW_ON_ERROR);

            $self->client->setResponse(
                $self->client->getClient()->request(
                    'PATCH',
                    "private/categories/{$categoryId}",
                    $headers,
                    $body
                )
            );

            $client->handleWrappedResponse($self->handleResponse(...));
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $client->getTaler()->getLogger()->error("Taler update category request failed: {$e->getCode()}, {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * @param InventoryClient $client
     * @param int $categoryId
     * @param CategoryCreateRequest $request
     * @param array<string, string> $headers
     * @return mixed
     */
    public static function runAsync(InventoryClient $client, int $categoryId, CategoryCreateRequest $request, array $headers = []): mixed
    {
        $self = new self($client);

        $body = json_encode($request, JSON_THROW_ON_ERROR);

        return $client
            ->getClient()
            ->requestAsync('PATCH', "private/categories/{$categoryId}", $headers, $body)
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



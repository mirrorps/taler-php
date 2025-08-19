<?php

namespace Taler\Api\Inventory\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Inventory\Dto\CategoryProductList;
use Taler\Api\Inventory\InventoryClient;
use Taler\Exception\TalerException;

class GetCategory
{
    public function __construct(
        private InventoryClient $client
    ) {}

    /**
     * @param InventoryClient $client
     * @param int $categoryId
     * @param array<string, string> $headers
     * @return CategoryProductList|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(InventoryClient $client, int $categoryId, array $headers = []): CategoryProductList|array
    {
        $self = new self($client);

        try {
            $self->client->setResponse(
                $self->client->getClient()->request('GET', "private/categories/{$categoryId}", $headers)
            );

            /** @var CategoryProductList|array{ name: string, name_i18n?: array<string, string>, products: array<int, array{product_id: string}> } $result */
            $result = $client->handleWrappedResponse($self->handleResponse(...));
            return $result;
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $client->getTaler()->getLogger()->error("Taler get category request failed: {$e->getCode()}, {$e->getMessage()}");
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
            ->requestAsync('GET', "private/categories/{$categoryId}", $headers)
            ->then(function (ResponseInterface $response) use ($self) {
                $self->client->setResponse($response);
                return $self->client->handleWrappedResponse($self->handleResponse(...));
            });
    }

    private function handleResponse(ResponseInterface $response): CategoryProductList
    {
        /** @var array{ name: string, name_i18n?: array<string, string>, products: array<int, array{product_id: string}> } $data */
        $data = $this->client->parseResponseBody($response, 200);
        return CategoryProductList::createFromArray($data);
    }
}



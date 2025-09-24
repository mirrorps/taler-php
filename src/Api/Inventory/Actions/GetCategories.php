<?php

namespace Taler\Api\Inventory\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Inventory\Dto\CategoryListResponse;
use Taler\Api\Inventory\InventoryClient;
use Taler\Exception\TalerException;

class GetCategories
{
    public function __construct(
        private InventoryClient $client
    ) {}

    /**
     * @param InventoryClient $client
     * @param array<string, string> $headers
     * @return CategoryListResponse|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(InventoryClient $client, array $headers = []): CategoryListResponse|array
    {
        $self = new self($client);

        try {
            $self->client->setResponse(
                $self->client->getClient()->request('GET', 'private/categories', $headers)
            );

            /** @var CategoryListResponse|array{categories: array<int, array{category_id: int, name: string, name_i18n?: array<string, string>, product_count: int}>} $result */
            $result = $client->handleWrappedResponse($self->handleResponse(...));
            return $result;
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $sanitized = \Taler\Helpers\sanitizeString((string) $e->getMessage());
            $client->getTaler()->getLogger()->error("Taler get categories request failed: {$e->getCode()}, {$sanitized}");
            throw $e;
        }
    }

    /**
     * @param InventoryClient $client
     * @param array<string, string> $headers
     * @return mixed
     */
    public static function runAsync(InventoryClient $client, array $headers = []): mixed
    {
        $self = new self($client);

        return $client
            ->getClient()
            ->requestAsync('GET', 'private/categories', $headers)
            ->then(function (ResponseInterface $response) use ($self) {
                $self->client->setResponse($response);
                return $self->client->handleWrappedResponse($self->handleResponse(...));
            });
    }

    private function handleResponse(ResponseInterface $response): CategoryListResponse
    {
        /** @var array{categories: array<int, array{category_id: int, name: string, name_i18n?: array<string, string>, product_count: int}>} $data */
        $data = $this->client->parseResponseBody($response, 200);
        return CategoryListResponse::createFromArray($data);
    }
}



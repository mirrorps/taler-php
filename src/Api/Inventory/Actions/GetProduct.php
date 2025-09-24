<?php

namespace Taler\Api\Inventory\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Inventory\Dto\ProductDetail;
use Taler\Api\Inventory\InventoryClient;
use Taler\Exception\TalerException;

class GetProduct
{
    public function __construct(
        private InventoryClient $client
    ) {}

    /**
     * @param InventoryClient $client
     * @param string $productId
     * @param array<string, string> $headers
     * @return ProductDetail|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(InventoryClient $client, string $productId, array $headers = []): ProductDetail|array
    {
        $self = new self($client);

        try {
            $self->client->setResponse(
                $self->client->getClient()->request('GET', "private/products/{$productId}", $headers)
            );

            /** @var ProductDetail|array<string,mixed> $result */
            $result = $client->handleWrappedResponse($self->handleResponse(...));
            return $result;
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $sanitized = \Taler\Helpers\sanitizeString((string) $e->getMessage());
            $client->getTaler()->getLogger()->error("Taler get product request failed: {$e->getCode()}, {$sanitized}");
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
            ->requestAsync('GET', "private/products/{$productId}", $headers)
            ->then(function (ResponseInterface $response) use ($self) {
                $self->client->setResponse($response);
                return $self->client->handleWrappedResponse($self->handleResponse(...));
            });
    }

    private function handleResponse(ResponseInterface $response): ProductDetail
    {
        /** @var array{
         *   product_name: string,
         *   description: string,
         *   description_i18n: array<string,string>,
         *   unit: string,
         *   categories: array<int,int>,
         *   price: string,
         *   image: string,
         *   taxes?: array<int, array{name: string, tax: string}>,
         *   total_stock: int,
         *   total_sold: int,
         *   total_lost: int,
         *   address?: array<string,mixed>,
         *   next_restock?: array{t_s: int|string},
         *   minimum_age?: int
         * } $data */
        $data = $this->client->parseResponseBody($response, 200);
        return ProductDetail::createFromArray($data);
    }
}



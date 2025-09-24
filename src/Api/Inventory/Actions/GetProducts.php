<?php

namespace Taler\Api\Inventory\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Inventory\Dto\GetProductsRequest;
use Taler\Api\Inventory\Dto\InventorySummaryResponse;
use Taler\Api\Inventory\InventoryClient;
use Taler\Exception\TalerException;

class GetProducts
{
    public function __construct(
        private InventoryClient $client
    ) {}

    /**
     * @param InventoryClient $client
     * @param GetProductsRequest|null $request
     * @param array<string, string> $headers
     * @return InventorySummaryResponse|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(InventoryClient $client, ?GetProductsRequest $request = null, array $headers = []): InventorySummaryResponse|array
    {
        $self = new self($client);

        try {
            $query = $request ? ('?' . http_build_query($request->toArray())) : '';

            $self->client->setResponse(
                $self->client->getClient()->request('GET', 'private/products' . $query, $headers)
            );

            /** @var InventorySummaryResponse|array{products: array<int, array{product_id: string, product_serial: int}>} $result */
            $result = $client->handleWrappedResponse($self->handleResponse(...));
            return $result;
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $sanitized = \Taler\Helpers\sanitizeString((string) $e->getMessage());
            $client->getTaler()->getLogger()->error("Taler get products request failed: {$e->getCode()}, {$sanitized}");
            throw $e;
        }
    }

    /**
     * @param InventoryClient $client
     * @param GetProductsRequest|null $request
     * @param array<string, string> $headers
     * @return mixed
     */
    public static function runAsync(InventoryClient $client, ?GetProductsRequest $request = null, array $headers = []): mixed
    {
        $self = new self($client);

        $query = $request ? ('?' . http_build_query($request->toArray())) : '';

        return $client
            ->getClient()
            ->requestAsync('GET', 'private/products' . $query, $headers)
            ->then(function (ResponseInterface $response) use ($self) {
                $self->client->setResponse($response);
                return $self->client->handleWrappedResponse($self->handleResponse(...));
            });
    }

    private function handleResponse(ResponseInterface $response): InventorySummaryResponse
    {
        /** @var array{products: array<int, array{product_id: string, product_serial: int}>} $data */
        $data = $this->client->parseResponseBody($response, 200);
        return InventorySummaryResponse::createFromArray($data);
    }
}



<?php

namespace Taler\Api\Inventory\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Inventory\Dto\FullInventoryDetailsResponse;
use Taler\Api\Inventory\InventoryClient;
use Taler\Exception\TalerException;

class GetPos
{
    public function __construct(
        private InventoryClient $client
    ) {}

    /**
     * @param InventoryClient $client
     * @param array<string, string> $headers
     * @return FullInventoryDetailsResponse|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(InventoryClient $client, array $headers = []): FullInventoryDetailsResponse|array
    {
        $self = new self($client);

        try {
            $self->client->setResponse(
                $self->client->getClient()->request('GET', 'private/pos', $headers)
            );

            /** @var FullInventoryDetailsResponse|array<string,mixed> $result */
            $result = $client->handleWrappedResponse($self->handleResponse(...));
            return $result;
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $client->getTaler()->getLogger()->error("Taler get POS request failed: {$e->getCode()}, {$e->getMessage()}");
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
            ->requestAsync('GET', 'private/pos', $headers)
            ->then(function (ResponseInterface $response) use ($self) {
                $self->client->setResponse($response);
                return $self->client->handleWrappedResponse($self->handleResponse(...));
            });
    }

    private function handleResponse(ResponseInterface $response): FullInventoryDetailsResponse
    {
        /** @var array{
         *   products: array<int, array{
         *     product_serial: int,
         *     product_id: string,
         *     product_name: string,
         *     categories: array<int,int>,
         *     description: string,
         *     description_i18n: array<string,string>,
         *     unit: string,
         *     price: string,
         *     image?: string,
         *     taxes?: array<int, array{name: string, tax: string}>,
         *     total_stock?: int,
         *     minimum_age?: int
         *   }>,
         *   categories: array<int, array{id: int, name: string, name_i18n?: array<string,string>}>} $data */
        $data = $this->client->parseResponseBody($response, 200);
        return FullInventoryDetailsResponse::createFromArray($data);
    }
}



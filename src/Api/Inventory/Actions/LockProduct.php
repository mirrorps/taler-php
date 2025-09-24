<?php

namespace Taler\Api\Inventory\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Inventory\Dto\LockRequest;
use Taler\Api\Inventory\InventoryClient;
use Taler\Exception\TalerException;

class LockProduct
{
    public function __construct(
        private InventoryClient $client
    ) {}

    /**
     * @param InventoryClient $client
     * @param string $productId
     * @param LockRequest $request
     * @param array<string, string> $headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(InventoryClient $client, string $productId, LockRequest $request, array $headers = []): void
    {
        $self = new self($client);

        try {
            $body = json_encode($request, JSON_THROW_ON_ERROR);

            $self->client->setResponse(
                $self->client->getClient()->request(
                    'POST',
                    "private/products/{$productId}/lock",
                    $headers,
                    $body
                )
            );

            $client->handleWrappedResponse($self->handleResponse(...));
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $sanitized = \Taler\Helpers\sanitizeString((string) $e->getMessage());
            $client->getTaler()->getLogger()->error("Taler lock product request failed: {$e->getCode()}, {$sanitized}");
            throw $e;
        }
    }

    /**
     * @param InventoryClient $client
     * @param string $productId
     * @param LockRequest $request
     * @param array<string, string> $headers
     * @return mixed
     */
    public static function runAsync(InventoryClient $client, string $productId, LockRequest $request, array $headers = []): mixed
    {
        $self = new self($client);

        $body = json_encode($request, JSON_THROW_ON_ERROR);

        return $client
            ->getClient()
            ->requestAsync('POST', "private/products/{$productId}/lock", $headers, $body)
            ->then(function (ResponseInterface $response) use ($self) {
                $self->client->setResponse($response);
                return $self->client->handleWrappedResponse($self->handleResponse(...));
            });
    }

    private function handleResponse(ResponseInterface $response): void
    {
        // 204 is success; 404 and 410 bubble up as errors
        $this->client->parseResponseBody($response, 204);
    }
}



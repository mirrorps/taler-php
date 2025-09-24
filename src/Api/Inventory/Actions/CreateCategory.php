<?php

namespace Taler\Api\Inventory\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Inventory\Dto\CategoryCreateRequest;
use Taler\Api\Inventory\Dto\CategoryCreatedResponse;
use Taler\Api\Inventory\InventoryClient;
use Taler\Exception\TalerException;

class CreateCategory
{
    public function __construct(
        private InventoryClient $client
    ) {}

    /**
     * @param InventoryClient $client
     * @param CategoryCreateRequest $request
     * @param array<string, string> $headers
     * @return CategoryCreatedResponse|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(InventoryClient $client, CategoryCreateRequest $request, array $headers = []): CategoryCreatedResponse|array
    {
        $self = new self($client);

        try {
            $body = json_encode($request, JSON_THROW_ON_ERROR);

            $self->client->setResponse(
                $self->client->getClient()->request(
                    'POST',
                    'private/categories',
                    $headers,
                    $body
                )
            );

            /** @var CategoryCreatedResponse|array{category_id: int} $result */
            $result = $client->handleWrappedResponse($self->handleResponse(...));
            return $result;
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $sanitized = \Taler\Helpers\sanitizeString((string) $e->getMessage());
            $client->getTaler()->getLogger()->error("Taler create category request failed: {$e->getCode()}, {$sanitized}");
            throw $e;
        }
    }

    /**
     * @param InventoryClient $client
     * @param CategoryCreateRequest $request
     * @param array<string, string> $headers
     * @return mixed
     */
    public static function runAsync(InventoryClient $client, CategoryCreateRequest $request, array $headers = []): mixed
    {
        $self = new self($client);

        $body = json_encode($request, JSON_THROW_ON_ERROR);

        return $client
            ->getClient()
            ->requestAsync('POST', 'private/categories', $headers, $body)
            ->then(function (ResponseInterface $response) use ($self) {
                $self->client->setResponse($response);
                return $self->client->handleWrappedResponse($self->handleResponse(...));
            });
    }

    private function handleResponse(ResponseInterface $response): CategoryCreatedResponse
    {
        /** @var array{category_id: int} $data */
        $data = $this->client->parseResponseBody($response, 200);
        return CategoryCreatedResponse::createFromArray($data);
    }
}



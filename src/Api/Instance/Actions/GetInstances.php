<?php

namespace Taler\Api\Instance\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Instance\Dto\InstancesResponse;
use Taler\Api\Instance\InstanceClient;
use Taler\Exception\TalerException;

class GetInstances
{
    public function __construct(
        private InstanceClient $instanceClient
    ) {}

    /**
     * Retrieve the list of all merchant instances (admin only).
     *
     * Endpoint: GET /management/instances
     *
     * @param InstanceClient $instanceClient
     * @param array<string, string> $headers
     * @return InstancesResponse|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(
        InstanceClient $instanceClient,
        array $headers = []
    ): InstancesResponse|array {
        $action = new self($instanceClient);

        try {
            $action->instanceClient->setResponse(
                $action->instanceClient->getClient()->request(
                    'GET',
                    'management/instances',
                    $headers
                )
            );

            /** @var InstancesResponse|array{instances: array<int, array{ name: string, id: string, merchant_pub: string, payment_targets: array<int, string>, deleted: bool, website?: string|null, logo?: string|null }>} $result */
            $result = $instanceClient->handleWrappedResponse($action->handleResponse(...));
            return $result;
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $sanitized = \Taler\Helpers\sanitizeString((string) $e->getMessage());
            $instanceClient->getTaler()->getLogger()->error("Taler get instances request failed: {$e->getCode()}, {$sanitized}");
            throw $e;
        }
    }

    /**
     * Async variant
     *
     * @param InstanceClient $instanceClient
     * @param array<string, string> $headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public static function runAsync(
        InstanceClient $instanceClient,
        array $headers = []
    ): mixed {
        $action = new self($instanceClient);

        return $instanceClient
            ->getClient()
            ->requestAsync(
                'GET',
                'management/instances',
                $headers
            )
            ->then(function (ResponseInterface $response) use ($action) {
                $action->instanceClient->setResponse($response);
                return $action->instanceClient->handleWrappedResponse($action->handleResponse(...));
            });
    }

    /**
     * Handle response 200 OK to InstancesResponse
     */
    private function handleResponse(ResponseInterface $response): InstancesResponse
    {
        $statusCode = $response->getStatusCode();
        /** @var array{instances: array<int, array{ name: string, id: string, merchant_pub: string, payment_targets: array<int, string>, deleted: bool, website?: string|null, logo?: string|null }>} $data */
        $data = $this->instanceClient->parseResponseBody($response, 200);
        return InstancesResponse::createFromArray($data);
    }
}



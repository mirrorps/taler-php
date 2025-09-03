<?php

namespace Taler\Api\Instance\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Instance\Dto\QueryInstancesResponse;
use Taler\Api\Instance\InstanceClient;
use Taler\Exception\TalerException;

class GetInstance
{
    public function __construct(
        private InstanceClient $instanceClient
    ) {}

    /**
     * Query a specific merchant instance.
     *
     * Endpoint: GET /instances/$INSTANCE/private
     *
     * @param InstanceClient $instanceClient
     * @param string $instanceId
     * @param array<string, string> $headers
     * @return QueryInstancesResponse|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(
        InstanceClient $instanceClient,
        string $instanceId,
        array $headers = []
    ): QueryInstancesResponse|array {
        $action = new self($instanceClient);

        try {
            $action->instanceClient->setResponse(
                $action->instanceClient->getClient()->request(
                    'GET',
                    "instances/{$instanceId}/private",
                    $headers
                )
            );

            /** @var QueryInstancesResponse|array{name: string, merchant_pub: string, address: array<string, mixed>, jurisdiction: array<string, mixed>, use_stefan: bool, default_wire_transfer_delay: array{d_us: int|string}, default_pay_delay: array{d_us: int|string}, auth: array{method: string}, email?: string|null, email_validated?: bool|null, phone_number?: string|null, phone_validated?: bool|null, website?: string|null, logo?: string|null} $result */
            $result = $instanceClient->handleWrappedResponse($action->handleResponse(...));
            return $result;
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $instanceClient->getTaler()->getLogger()->error("Taler get instance request failed: {$e->getCode()}, {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Async variant
     *
     * @param InstanceClient $instanceClient
     * @param string $instanceId
     * @param array<string, string> $headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public static function runAsync(
        InstanceClient $instanceClient,
        string $instanceId,
        array $headers = []
    ): mixed {
        $action = new self($instanceClient);

        return $instanceClient
            ->getClient()
            ->requestAsync(
                'GET',
                "instances/{$instanceId}/private",
                $headers
            )
            ->then(function (ResponseInterface $response) use ($action) {
                $action->instanceClient->setResponse($response);
                return $action->instanceClient->handleWrappedResponse($action->handleResponse(...));
            });
    }

    /**
     * Handle response 200 OK to QueryInstancesResponse
     */
    private function handleResponse(ResponseInterface $response): QueryInstancesResponse
    {
        $statusCode = $response->getStatusCode();
        /** @var array{name: string, merchant_pub: string, address: array<string, mixed>, jurisdiction: array<string, mixed>, use_stefan: bool, default_wire_transfer_delay: array{d_us: int|string}, default_pay_delay: array{d_us: int|string}, auth: array{method: string}, email?: string|null, email_validated?: bool|null, phone_number?: string|null, phone_validated?: bool|null, website?: string|null, logo?: string|null} $data */
        $data = $this->instanceClient->parseResponseBody($response, 200);
        return QueryInstancesResponse::createFromArray($data);
    }
}



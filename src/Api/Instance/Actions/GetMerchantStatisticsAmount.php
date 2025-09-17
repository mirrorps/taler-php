<?php

namespace Taler\Api\Instance\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Instance\Dto\GetMerchantStatisticsAmountRequest;
use Taler\Api\Instance\Dto\MerchantStatisticsAmountResponse;
use Taler\Api\Instance\InstanceClient;
use Taler\Exception\TalerException;

class GetMerchantStatisticsAmount
{
    public function __construct(
        private InstanceClient $instanceClient
    ) {}

    /**
     * Retrieve merchant statistics where values are amounts for the given $SLUG.
     *
     * Endpoint: GET /instances/$INSTANCE/private/statistics-amount/$SLUG
     *
     * @param InstanceClient $instanceClient
     * @param string $instanceId
     * @param string $slug
     * @param GetMerchantStatisticsAmountRequest|null $request
     * @param array<string, string> $headers
     * @return MerchantStatisticsAmountResponse|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(
        InstanceClient $instanceClient,
        string $instanceId,
        string $slug,
        ?GetMerchantStatisticsAmountRequest $request = null,
        array $headers = []
    ): MerchantStatisticsAmountResponse|array {
        $action = new self($instanceClient);

        try {
            $params = $request?->toArray() ?? [];

            $action->instanceClient->setResponse(
                $action->instanceClient->getClient()->request(
                    'GET',
                    "instances/{$instanceId}/private/statistics-amount/{$slug}?" . http_build_query($params),
                    $headers
                )
            );

            /** @var MerchantStatisticsAmountResponse|array<string, mixed> $result */
            $result = $instanceClient->handleWrappedResponse($action->handleResponse(...));
            return $result;
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $instanceClient->getTaler()->getLogger()->error("Taler get merchant statistics amount request failed: {$e->getCode()}, {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Async variant
     *
     * @param InstanceClient $instanceClient
     * @param string $instanceId
     * @param string $slug
     * @param GetMerchantStatisticsAmountRequest|null $request
     * @param array<string, string> $headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public static function runAsync(
        InstanceClient $instanceClient,
        string $instanceId,
        string $slug,
        ?GetMerchantStatisticsAmountRequest $request = null,
        array $headers = []
    ): mixed {
        $action = new self($instanceClient);

        $params = $request?->toArray() ?? [];

        return $instanceClient
            ->getClient()
            ->requestAsync(
                'GET',
                "instances/{$instanceId}/private/statistics-amount/{$slug}?" . http_build_query($params),
                $headers
            )
            ->then(function (ResponseInterface $response) use ($action) {
                $action->instanceClient->setResponse($response);
                return $action->instanceClient->handleWrappedResponse($action->handleResponse(...));
            });
    }

    /**
     * Handle response: 200 -> MerchantStatisticsAmountResponse
     */
    private function handleResponse(ResponseInterface $response): MerchantStatisticsAmountResponse
    {
        $data = $this->instanceClient->parseResponseBody($response, 200);
        return MerchantStatisticsAmountResponse::createFromArray($data);
    }
}



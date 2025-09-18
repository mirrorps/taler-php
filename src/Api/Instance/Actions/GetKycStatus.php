<?php

namespace Taler\Api\Instance\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Instance\Dto\GetKycStatusRequest;
use Taler\Api\Instance\Dto\MerchantAccountKycRedirectsResponse;
use Taler\Api\Instance\InstanceClient;
use Taler\Exception\TalerException;

class GetKycStatus
{
    public function __construct(
        private InstanceClient $instanceClient
    ) {}

    /**
     * Check KYC status of a particular payment target for the given instance.
     *
     * Endpoint: GET /instances/$INSTANCE/private/kyc
     *
     * @param InstanceClient $instanceClient
     * @param string $instanceId
     * @param GetKycStatusRequest|null $request
     * @param array<string, string> $headers
     * @return MerchantAccountKycRedirectsResponse|array<string, mixed>|null Null for 204
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(
        InstanceClient $instanceClient,
        string $instanceId,
        ?GetKycStatusRequest $request = null,
        array $headers = []
    ): MerchantAccountKycRedirectsResponse|array|null {
        $action = new self($instanceClient);

        try {
            $params = $request?->toArray() ?? [];

            $action->instanceClient->setResponse(
                $action->instanceClient->getClient()->request(
                    'GET',
                    "instances/{$instanceId}/private/kyc?" . http_build_query($params),
                    $headers
                )
            );

            /** @var MerchantAccountKycRedirectsResponse|array<string, mixed>|null $result */
            $result = $instanceClient->handleWrappedResponse($action->handleResponse(...));
            return $result;
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $instanceClient->getTaler()->getLogger()->error("Taler get KYC status request failed: {$e->getCode()}, {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Async variant
     *
     * @param InstanceClient $instanceClient
     * @param string $instanceId
     * @param GetKycStatusRequest|null $request
     * @param array<string, string> $headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public static function runAsync(
        InstanceClient $instanceClient,
        string $instanceId,
        ?GetKycStatusRequest $request = null,
        array $headers = []
    ): mixed {
        $action = new self($instanceClient);

        $params = $request?->toArray() ?? [];

        return $instanceClient
            ->getClient()
            ->requestAsync(
                'GET',
                "instances/{$instanceId}/private/kyc?" . http_build_query($params),
                $headers
            )
            ->then(function (ResponseInterface $response) use ($action) {
                $action->instanceClient->setResponse($response);
                return $action->instanceClient->handleWrappedResponse($action->handleResponse(...));
            });
    }

    /**
     * Handle response: 200 -> MerchantAccountKycRedirectsResponse, 204 -> null
     */
    private function handleResponse(ResponseInterface $response): MerchantAccountKycRedirectsResponse|null
    {
        $statusCode = $response->getStatusCode();
        if ($statusCode === 204) {
            return null;
        }

        $data = $this->instanceClient->parseResponseBody($response, 200);
        return MerchantAccountKycRedirectsResponse::createFromArray($data);
    }
}




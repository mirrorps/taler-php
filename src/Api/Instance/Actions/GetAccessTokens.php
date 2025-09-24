<?php

namespace Taler\Api\Instance\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Instance\Dto\GetAccessTokensRequest;
use Taler\Api\Instance\Dto\TokenInfos;
use Taler\Api\Instance\InstanceClient;
use Taler\Exception\TalerException;

class GetAccessTokens
{
    public function __construct(
        private InstanceClient $instanceClient
    ) {}

    /**
     * Retrieve a list of issued access tokens for $INSTANCE.
     *
     * @param InstanceClient $instanceClient
     * @param string $instanceId
     * @param GetAccessTokensRequest|null $request
     * @param array<string, string> $headers
     * @return TokenInfos|array<string, mixed>|null Null for 204
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(
        InstanceClient $instanceClient,
        string $instanceId,
        ?GetAccessTokensRequest $request = null,
        array $headers = []
    ): TokenInfos|array|null {
        $action = new self($instanceClient);

        try {
            $params = $request?->toArray() ?? [];

            $action->instanceClient->setResponse(
                $action->instanceClient->getClient()->request(
                    'GET',
                    "instances/{$instanceId}/private/tokens?" . http_build_query($params),
                    $headers
                )
            );

            /** @var TokenInfos|array{tokens: array<int, array{creation_time: array{t_s: int|string}, expiration: array{t_s: int|string}, scope: string, refreshable: bool, description?: string|null, serial: int}>}|null $result */
            $result = $instanceClient->handleWrappedResponse($action->handleResponse(...));
            return $result;
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $sanitized = \Taler\Helpers\sanitizeString((string) $e->getMessage());
            $instanceClient->getTaler()->getLogger()->error("Taler get access tokens request failed: {$e->getCode()}, {$sanitized}");
            throw $e;
        }
    }

    /**
     * Async variant
     *
     * @param InstanceClient $instanceClient
     * @param string $instanceId
     * @param GetAccessTokensRequest|null $request
     * @param array<string, string> $headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public static function runAsync(
        InstanceClient $instanceClient,
        string $instanceId,
        ?GetAccessTokensRequest $request = null,
        array $headers = []
    ): mixed {
        $action = new self($instanceClient);

        $params = $request?->toArray() ?? [];

        return $instanceClient
            ->getClient()
            ->requestAsync(
                'GET',
                "instances/{$instanceId}/private/tokens?" . http_build_query($params),
                $headers
            )
            ->then(function (ResponseInterface $response) use ($action) {
                $action->instanceClient->setResponse($response);
                return $action->instanceClient->handleWrappedResponse($action->handleResponse(...));
            });
    }

    /**
     * Handle response: 200 -> TokenInfos, 204 -> null
     */
    private function handleResponse(ResponseInterface $response): TokenInfos|null
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode === 204) {
            return null;
        }

        $data = $this->instanceClient->parseResponseBody($response, 200);
        return TokenInfos::createFromArray($data);
    }
}



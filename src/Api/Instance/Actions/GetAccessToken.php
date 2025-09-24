<?php

namespace Taler\Api\Instance\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Instance\Dto\LoginTokenRequest;
use Taler\Api\Instance\Dto\LoginTokenSuccessResponse;
use Taler\Api\Instance\InstanceClient;
use Taler\Exception\TalerException;

class GetAccessToken
{
    public function __construct(
        private InstanceClient $instanceClient
    ) {}

    /**
     * Retrieve an access token for the merchant API for instance $INSTANCE.
     *
     * Endpoint: POST /instances/$INSTANCE/private/token
     *
     * @param InstanceClient $instanceClient
     * @param string $instanceId
     * @param LoginTokenRequest $loginTokenRequest
     * @param array<string, string> $headers
     * @return LoginTokenSuccessResponse|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(
        InstanceClient $instanceClient,
        string $instanceId,
        LoginTokenRequest $loginTokenRequest,
        array $headers = []
    ): LoginTokenSuccessResponse|array {
        $action = new self($instanceClient);

        try {
            $requestBody = json_encode($loginTokenRequest, JSON_THROW_ON_ERROR);

            $action->instanceClient->setResponse(
                $action->instanceClient->getClient()->request(
                    'POST',
                    "instances/{$instanceId}/private/token",
                    $headers,
                    $requestBody
                )
            );

            /** @var LoginTokenSuccessResponse|array{token?: string|null, access_token: string, scope: string, expiration: array{t_s: int|string}, refreshable: bool} $result */
            $result = $instanceClient->handleWrappedResponse($action->handleResponse(...));
            return $result;
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $sanitized = \Taler\Helpers\sanitizeString((string) $e->getMessage());
            $instanceClient->getTaler()->getLogger()->error("Taler get access token request failed: {$e->getCode()}, {$sanitized}");
            throw $e;
        }
    }

    /**
     * Retrieve an access token asynchronously.
     *
     * @param InstanceClient $instanceClient
     * @param string $instanceId
     * @param LoginTokenRequest $loginTokenRequest
     * @param array<string, string> $headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public static function runAsync(
        InstanceClient $instanceClient,
        string $instanceId,
        LoginTokenRequest $loginTokenRequest,
        array $headers = []
    ): mixed {
        $action = new self($instanceClient);

        $requestBody = json_encode($loginTokenRequest, JSON_THROW_ON_ERROR);

        return $instanceClient
            ->getClient()
            ->requestAsync(
                'POST',
                "instances/{$instanceId}/private/token",
                $headers,
                $requestBody
            )
            ->then(function (ResponseInterface $response) use ($action) {
                $action->instanceClient->setResponse($response);
                return $action->instanceClient->handleWrappedResponse($action->handleResponse(...));
            });
    }

    /**
     * Handle 200 OK response
     */
    private function handleResponse(ResponseInterface $response): LoginTokenSuccessResponse
    {
        // Read status code explicitly to satisfy expectations and mirror other actions
        $statusCode = $response->getStatusCode();
        $data = $this->instanceClient->parseResponseBody($response, 200);
        return LoginTokenSuccessResponse::createFromArray($data);
    }
}



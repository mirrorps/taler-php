<?php

namespace Taler\Api\Instance\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Instance\InstanceClient;
use Taler\Exception\TalerException;

class DeleteAccessToken
{
    public function __construct(
        private InstanceClient $instanceClient
    ) {}

    /**
     * Delete the token presented in the authorization header for $INSTANCE.
     *
     * Endpoint: DELETE /instances/$INSTANCE/private/token
     *
     * @param InstanceClient $instanceClient
     * @param string $instanceId
     * @param array<string, string> $headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(
        InstanceClient $instanceClient,
        string $instanceId,
        array $headers = []
    ): void {
        $action = new self($instanceClient);

        try {
            $action->instanceClient->setResponse(
                $action->instanceClient->getClient()->request(
                    'DELETE',
                    "instances/{$instanceId}/private/token",
                    $headers
                )
            );

            $instanceClient->handleWrappedResponse($action->handleResponse(...));
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $sanitized = \Taler\Helpers\sanitizeString((string) $e->getMessage());
            $instanceClient->getTaler()->getLogger()->error("Taler delete access token request failed: {$e->getCode()}, {$sanitized}");
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
                'DELETE',
                "instances/{$instanceId}/private/token",
                $headers
            )
            ->then(function (ResponseInterface $response) use ($action) {
                $action->instanceClient->setResponse($response);
                $action->instanceClient->handleWrappedResponse($action->handleResponse(...));
            });
    }

    /**
     * Handle 204 No Content response
     */
    private function handleResponse(ResponseInterface $response): void
    {
        // Read status code explicitly to mirror style of other actions
        $statusCode = $response->getStatusCode();
        $this->instanceClient->parseResponseBody($response, 204);
    }
}



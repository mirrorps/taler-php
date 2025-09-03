<?php

namespace Taler\Api\Instance\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Instance\InstanceClient;
use Taler\Exception\TalerException;

class DeleteAccessTokenBySerial
{
    public function __construct(
        private InstanceClient $instanceClient
    ) {}

    /**
     * Delete a token for $INSTANCE API access by its $SERIAL.
     *
     * Endpoint: DELETE /instances/$INSTANCE/private/tokens/$SERIAL
     *
     * @param InstanceClient $instanceClient
     * @param string $instanceId
     * @param int $serial
     * @param array<string, string> $headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(
        InstanceClient $instanceClient,
        string $instanceId,
        int $serial,
        array $headers = []
    ): void {
        $action = new self($instanceClient);

        try {
            $action->instanceClient->setResponse(
                $action->instanceClient->getClient()->request(
                    'DELETE',
                    "instances/{$instanceId}/private/tokens/{$serial}",
                    $headers
                )
            );

            $instanceClient->handleWrappedResponse($action->handleResponse(...));
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $instanceClient->getTaler()->getLogger()->error("Taler delete access token by serial request failed: {$e->getCode()}, {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Async variant
     *
     * @param InstanceClient $instanceClient
     * @param string $instanceId
     * @param int $serial
     * @param array<string, string> $headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public static function runAsync(
        InstanceClient $instanceClient,
        string $instanceId,
        int $serial,
        array $headers = []
    ): mixed {
        $action = new self($instanceClient);

        return $instanceClient
            ->getClient()
            ->requestAsync(
                'DELETE',
                "instances/{$instanceId}/private/tokens/{$serial}",
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
        $statusCode = $response->getStatusCode();
        $this->instanceClient->parseResponseBody($response, 204);
    }
}



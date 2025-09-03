<?php

namespace Taler\Api\Instance\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Instance\Dto\InstanceReconfigurationMessage;
use Taler\Api\Instance\InstanceClient;
use Taler\Exception\TalerException;

class UpdateInstance
{
    public function __construct(
        private InstanceClient $instanceClient
    ) {}

    /**
     * Update the configuration of a merchant instance.
     *
     * Endpoint: PATCH /instances/$INSTANCE/private
     *
     * @param InstanceClient $instanceClient
     * @param string $instanceId
     * @param InstanceReconfigurationMessage $message
     * @param array<string, string> $headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(
        InstanceClient $instanceClient,
        string $instanceId,
        InstanceReconfigurationMessage $message,
        array $headers = []
    ): void {
        $action = new self($instanceClient);

        try {
            $requestBody = json_encode($message, JSON_THROW_ON_ERROR);

            $action->instanceClient->setResponse(
                $action->instanceClient->getClient()->request(
                    'PATCH',
                    "instances/{$instanceId}/private",
                    $headers,
                    $requestBody
                )
            );

            $instanceClient->handleWrappedResponse($action->handleResponse(...));
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $instanceClient->getTaler()->getLogger()->error("Taler update instance request failed: {$e->getCode()}, {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Async variant
     *
     * @param InstanceClient $instanceClient
     * @param string $instanceId
     * @param InstanceReconfigurationMessage $message
     * @param array<string, string> $headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public static function runAsync(
        InstanceClient $instanceClient,
        string $instanceId,
        InstanceReconfigurationMessage $message,
        array $headers = []
    ): mixed {
        $action = new self($instanceClient);

        $requestBody = json_encode($message, JSON_THROW_ON_ERROR);

        return $instanceClient
            ->getClient()
            ->requestAsync(
                'PATCH',
                "instances/{$instanceId}/private",
                $headers,
                $requestBody
            )
            ->then(function (ResponseInterface $response) use ($action) {
                $action->instanceClient->setResponse($response);
                return $action->instanceClient->handleWrappedResponse($action->handleResponse(...));
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



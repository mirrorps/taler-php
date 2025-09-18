<?php

namespace Taler\Api\Instance\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Instance\Dto\Challenge;
use Taler\Api\Instance\InstanceClient;
use Taler\Exception\TalerException;

class DeleteInstance
{
    public function __construct(
        private InstanceClient $instanceClient
    ) {}

    /**
     * Delete (disable) or purge a merchant instance.
     *
     * Endpoint: DELETE /instances/$INSTANCE/private
     * Query parameter: purge=YES to fully purge. Default disables only.
     *
     * @param InstanceClient $instanceClient
     * @param string $instanceId
     * @param bool $purge If true, include purge=YES
     * @param array<string, string> $headers
     * @return Challenge|null Returns Challenge if 2FA is required (202), null on success (204)
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(
        InstanceClient $instanceClient,
        string $instanceId,
        bool $purge = false,
        array $headers = []
    ): ?Challenge {
        $action = new self($instanceClient);

        try {
            $query = $purge ? '?purge=YES' : '';

            $action->instanceClient->setResponse(
                $action->instanceClient->getClient()->request(
                    'DELETE',
                    "instances/{$instanceId}/private{$query}",
                    $headers
                )
            );

            return $action->handleResponse($action->instanceClient->getResponse());
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $instanceClient->getTaler()->getLogger()->error("Taler delete instance request failed: {$e->getCode()}, {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Async variant
     *
     * @param InstanceClient $instanceClient
     * @param string $instanceId
     * @param bool $purge If true, include purge=YES
     * @param array<string, string> $headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public static function runAsync(
        InstanceClient $instanceClient,
        string $instanceId,
        bool $purge = false,
        array $headers = []
    ): mixed {
        $action = new self($instanceClient);

        $query = $purge ? '?purge=YES' : '';

        return $instanceClient
            ->getClient()
            ->requestAsync(
                'DELETE',
                "instances/{$instanceId}/private{$query}",
                $headers
            )
            ->then(function (ResponseInterface $response) use ($action) {
                $action->instanceClient->setResponse($response);
                return $action->handleResponse($response);
            });
    }

    /**
     * Handle 202 Challenge or 204 No Content
     *
     * @return Challenge|null
     */
    private function handleResponse(ResponseInterface $response): ?Challenge
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode === 202) {
            $data = $this->instanceClient->parseResponseBody($response, 202);
            return Challenge::createFromArray($data);
        }

        $this->instanceClient->parseResponseBody($response, 204);

        return null;
    }
}



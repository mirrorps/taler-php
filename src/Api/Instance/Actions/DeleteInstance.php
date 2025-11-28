<?php

namespace Taler\Api\Instance\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\TwoFactorAuth\Dto\ChallengeResponse;
use Taler\Api\Instance\InstanceClient;
use Taler\Exception\TalerException;

use const Taler\Http\HTTP_STATUS_CODE_ACCEPTED;
use const Taler\Http\HTTP_STATUS_CODE_NO_CONTENT;

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
     * @return ChallengeResponse|null Returns ChallengeResponse if 2FA is required (202), null on success (204)
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(
        InstanceClient $instanceClient,
        string $instanceId,
        bool $purge = false,
        array $headers = []
    ): ?ChallengeResponse {
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
            $sanitized = \Taler\Helpers\sanitizeString((string) $e->getMessage());
            $instanceClient->getTaler()->getLogger()->error("Taler delete instance request failed: {$e->getCode()}, {$sanitized}");
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
     * Handle 202 ChallengeResponse or 204 No Content
     *
     * @return ChallengeResponse|null
     */
    private function handleResponse(ResponseInterface $response): ?ChallengeResponse
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode === HTTP_STATUS_CODE_ACCEPTED) {
            $data = $this->instanceClient->parseResponseBody($response, HTTP_STATUS_CODE_ACCEPTED);
            return ChallengeResponse::createFromArray($data);
        }

        $this->instanceClient->parseResponseBody($response, HTTP_STATUS_CODE_NO_CONTENT);

        return null;
    }
}



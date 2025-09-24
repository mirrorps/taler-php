<?php

namespace Taler\Api\Instance\Actions;

use Taler\Api\Instance\Dto\InstanceAuthConfigToken;
use Taler\Api\Instance\Dto\InstanceAuthConfigTokenOLD;
use Taler\Api\Instance\Dto\InstanceAuthConfigExternal;
use Taler\Api\Instance\Dto\Challenge;
use Taler\Api\Instance\InstanceClient;
use Taler\Exception\TalerException;
use Psr\Http\Message\ResponseInterface;

/**
 * Action for updating the authentication settings for an instance.
 *
 * Endpoint: POST /instances/$INSTANCE/private/auth
 * Required permission: instances-auth-write
 *
 * @since v21 (2FA challenge possible)
 */
class UpdateAuth
{
    public function __construct(
        private InstanceClient $instanceClient
    ) {}

    /**
     * Updates the authentication settings for a merchant instance.
     *
     * @param InstanceClient $instanceClient
     * @param string $instanceId The instance ID
     * @param InstanceAuthConfigToken|InstanceAuthConfigTokenOLD|InstanceAuthConfigExternal $authConfig The authentication configuration
     * @param array<string, string> $headers Optional request headers
     * @return Challenge|null Returns Challenge if 2FA is required (202), null on success (204)
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(
        InstanceClient $instanceClient,
        string $instanceId,
        InstanceAuthConfigToken|InstanceAuthConfigTokenOLD|InstanceAuthConfigExternal $authConfig,
        array $headers = []
    ): ?Challenge {
        $action = new self($instanceClient);

        try {
            $requestBody = json_encode($authConfig, JSON_THROW_ON_ERROR);

            $action->instanceClient->setResponse(
                $action->instanceClient->getClient()->request(
                    "POST",
                    "instances/{$instanceId}/private/auth",
                    $headers,
                    $requestBody
                )
            );

            return $action->handleResponse($action->instanceClient->getResponse());
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $sanitized = \Taler\Helpers\sanitizeString((string) $e->getMessage());
            $instanceClient->getTaler()->getLogger()->error("Taler update auth request failed: {$e->getCode()}, {$sanitized}");
            throw $e;
        }
    }

    /**
     * Updates the authentication settings for a merchant instance asynchronously.
     *
     * @param InstanceClient $instanceClient
     * @param string $instanceId The instance ID
     * @param InstanceAuthConfigToken|InstanceAuthConfigTokenOLD|InstanceAuthConfigExternal $authConfig The authentication configuration
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public static function runAsync(
        InstanceClient $instanceClient,
        string $instanceId,
        InstanceAuthConfigToken|InstanceAuthConfigTokenOLD|InstanceAuthConfigExternal $authConfig,
        array $headers = []
    ): mixed {
        $action = new self($instanceClient);

        $requestBody = json_encode($authConfig, JSON_THROW_ON_ERROR);

        return $instanceClient
            ->getClient()
            ->requestAsync(
                "POST",
                "instances/{$instanceId}/private/auth",
                $headers,
                $requestBody
            )
            ->then(function (ResponseInterface $response) use ($action) {
                $action->instanceClient->setResponse($response);
                return $action->handleResponse($response);
            });
    }

    /**
     * Handles the response from the update auth request.
     *
     * @param ResponseInterface $response
     * @return Challenge|null Returns Challenge if 2FA is required, null on success
     * @throws TalerException
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



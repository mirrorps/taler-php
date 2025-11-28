<?php

namespace Taler\Api\Instance\Actions;

use Taler\Api\Instance\Dto\InstanceAuthConfigToken;
use Taler\Api\Instance\Dto\InstanceAuthConfigTokenOLD;
use Taler\Api\Instance\Dto\InstanceAuthConfigExternal;
use Taler\Api\Instance\Dto\Challenge;
use Taler\Api\Instance\InstanceClient;
use Taler\Exception\TalerException;
use Psr\Http\Message\ResponseInterface;

use const Taler\Http\HTTP_STATUS_CODE_ACCEPTED;

/**
 * Action for resetting instance password.
 *
 * @since v21
 */
class ForgotPassword
{
    public function __construct(
        private InstanceClient $instanceClient
    ) {}

    /**
     * Resets the password for a merchant instance.
     *
     * @param InstanceClient $instanceClient
     * @param string $instanceId The instance ID
     * @param InstanceAuthConfigToken|InstanceAuthConfigTokenOLD|InstanceAuthConfigExternal $authConfig The new authentication configuration
     * @param array<string, string> $headers Optional request headers
     * @return Challenge|null Returns Challenge if 2FA is required, null on success
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(
        InstanceClient $instanceClient,
        string $instanceId,
        InstanceAuthConfigToken|InstanceAuthConfigTokenOLD|InstanceAuthConfigExternal $authConfig,
        array $headers = []
    ): ?Challenge {
        $forgotPassword = new self($instanceClient);

        try {
            $requestBody = json_encode($authConfig, JSON_THROW_ON_ERROR);

            $forgotPassword->instanceClient->setResponse(
                $forgotPassword->instanceClient->getClient()->request(
                    "POST",
                    "instances/{$instanceId}/forgot-password",
                    $headers,
                    $requestBody
                )
            );

            return $forgotPassword->handleResponse($forgotPassword->instanceClient->getResponse());
        } catch (TalerException $e) {
            //--- // NOTE: Logging is not necessary here; TalerException is already logged in HttpClientWrapper::run.
            throw $e;
        }
        catch (\Throwable $e) {
            $sanitized = \Taler\Helpers\sanitizeString((string) $e->getMessage());
            $instanceClient->getTaler()->getLogger()->error("Taler forgot password request failed: {$e->getCode()}, {$sanitized}");
            throw $e;
        }
    }

    /**
     * Resets the password for a merchant instance asynchronously.
     *
     * @param InstanceClient $instanceClient
     * @param string $instanceId The instance ID
     * @param InstanceAuthConfigToken|InstanceAuthConfigTokenOLD|InstanceAuthConfigExternal $authConfig The new authentication configuration
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
        $forgotPassword = new self($instanceClient);

        $requestBody = json_encode($authConfig, JSON_THROW_ON_ERROR);

        return $instanceClient
            ->getClient()
            ->requestAsync(
                "POST",
                "instances/{$instanceId}/forgot-password",
                $headers,
                $requestBody
            )
            ->then(function (ResponseInterface $response) use ($forgotPassword) {
                $forgotPassword->instanceClient->setResponse($response);
                return $forgotPassword->handleResponse($response);
            });
    }

    /**
     * Handles the response from the forgot password request.
     *
     * @param ResponseInterface $response
     * @return Challenge|null Returns Challenge if 2FA is required, null on success
     * @throws TalerException
     */
    private function handleResponse(ResponseInterface $response): ?Challenge
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode === HTTP_STATUS_CODE_ACCEPTED) {
            // 2FA required - return challenge
            $data = $this->instanceClient->parseResponseBody($response, 202);
            return Challenge::createFromArray($data);
        }
        
        $this->instanceClient->parseResponseBody($response, 204);
        
        return null;
    }
}

<?php

namespace Taler\Api\Instance\Actions;

use Taler\Api\Instance\Dto\InstanceConfigurationMessage;
use Taler\Api\Instance\InstanceClient;
use Taler\Exception\TalerException;
use Psr\Http\Message\ResponseInterface;
use Taler\Api\TwoFactorAuth\Dto\ChallengeResponse;

use const Taler\Http\HTTP_STATUS_CODE_ACCEPTED;
use const Taler\Http\HTTP_STATUS_CODE_NO_CONTENT;
use const Taler\Http\HTTP_STATUS_CODE_CONFLICT;
use const Taler\Http\HTTP_STATUS_CODE_SUCCESS;

/**
 * Action for creating a new merchant instance.
 */
class CreateInstance
{
    public function __construct(
        private InstanceClient $instanceClient
    ) {}

    /**
     * Creates a new merchant instance.
     *
     * @param InstanceClient $instanceClient
     * @param InstanceConfigurationMessage $instanceConfiguration The instance configuration data
     * @param array<string, string> $headers Optional request headers
     * @return null|ChallengeResponse Returns ChallengeResponse if 2FA is required (202), otherwise null
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(
        InstanceClient $instanceClient,
        InstanceConfigurationMessage $instanceConfiguration,
        array $headers = []
    ): null|ChallengeResponse {
        $createInstance = new self($instanceClient);

        try {
            $requestBody = json_encode($instanceConfiguration, JSON_THROW_ON_ERROR);

            $createInstance->instanceClient->setResponse(
                $createInstance->instanceClient->getClient()->request(
                    "POST",
                    "instances",
                    $headers,
                    $requestBody
                )
            );

            return $createInstance->handleResponse($createInstance->instanceClient->getResponse());
        } catch (TalerException $e) {
            //--- // NOTE: Logging is not necessary here; TalerException is already logged in HttpClientWrapper::run.
            throw $e;
        }
        catch (\Throwable $e) {
            $sanitized = \Taler\Helpers\sanitizeString((string) $e->getMessage());
            $instanceClient->getTaler()->getLogger()->error("Taler create instance request failed: {$e->getCode()}, {$sanitized}");
            throw $e;
        }
    }

    /**
     * Creates a new merchant instance asynchronously.
     *
     * @param InstanceClient $instanceClient
     * @param InstanceConfigurationMessage $instanceConfiguration The instance configuration data
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public static function runAsync(
        InstanceClient $instanceClient,
        InstanceConfigurationMessage $instanceConfiguration,
        array $headers = []
    ): mixed {
        $createInstance = new self($instanceClient);

        $requestBody = json_encode($instanceConfiguration, JSON_THROW_ON_ERROR);

        return $instanceClient
            ->getClient()
            ->requestAsync(
                "POST",
                "instances",
                $headers,
                $requestBody
            )
            ->then(function (ResponseInterface $response) use ($createInstance) {
                $createInstance->instanceClient->setResponse($response);
                $createInstance->handleResponse($response);
                return null;
            });
    }

    /**
     * Handles the response from the create instance request.
     *
     * @param ResponseInterface $response
     * @return null|ChallengeResponse
     * @throws TalerException
     */
    private function handleResponse(ResponseInterface $response): null|ChallengeResponse
    {
        $data = json_decode((string)$response->getBody(), true);

        return match ($response->getStatusCode()) {
            HTTP_STATUS_CODE_SUCCESS    => null, //-- success
            HTTP_STATUS_CODE_NO_CONTENT => null, //-- success / no content
            HTTP_STATUS_CODE_ACCEPTED   => ChallengeResponse::createFromArray($data), //-- 2FA required
            HTTP_STATUS_CODE_CONFLICT   => throw new TalerException(
                message: 'Instance creation failed: ' . $response->getReasonPhrase(),
                code: $response->getStatusCode(),
                response: $response
            ),
            default => throw new TalerException(
                message: 'Unexpected response status code: ' . $response->getStatusCode(),
                code: $response->getStatusCode(),
                response: $response
            )
        };
    }
}

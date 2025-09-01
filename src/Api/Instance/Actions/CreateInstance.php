<?php

namespace Taler\Api\Instance\Actions;

use Taler\Api\Instance\Dto\InstanceConfigurationMessage;
use Taler\Api\Instance\InstanceClient;
use Taler\Exception\TalerException;
use Psr\Http\Message\ResponseInterface;

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
     * @return void
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(
        InstanceClient $instanceClient,
        InstanceConfigurationMessage $instanceConfiguration,
        array $headers = []
    ): void {
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

            $createInstance->handleResponse($createInstance->instanceClient->getResponse());
        } catch (TalerException $e) {
            //--- // NOTE: Logging is not necessary here; TalerException is already logged in HttpClientWrapper::run.
            throw $e;
        }
        catch (\Throwable $e) {
            $instanceClient->getTaler()->getLogger()->error("Taler create instance request failed: {$e->getCode()}, {$e->getMessage()}");
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
     * @return void
     * @throws TalerException
     */
    private function handleResponse(ResponseInterface $response): void
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode === 204) {
            // Success - instance created
            return;
        }

        if ($statusCode === 409) {
            // Conflict - instance already exists or configuration conflict
            throw new TalerException(
                'Instance creation failed: ' . $response->getReasonPhrase(),
                $statusCode
            );
        }

        // Handle other status codes
        $this->instanceClient->parseResponseBody($response, 204);
    }
}

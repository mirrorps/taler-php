<?php

namespace Taler\Api\OtpDevices\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\OtpDevices\OtpDevicesClient;
use Taler\Exception\TalerException;

class DeleteOtpDevice
{
    public function __construct(
        private OtpDevicesClient $client
    ) {}

    /**
     * Delete a single OTP device.
     *
     * @param OtpDevicesClient $client
     * @param string $deviceId
     * @param array<string, string> $headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     * @see https://docs.taler.net/core/api-merchant.html#delete-[-instances-$INSTANCE]-private-otp-devices-$DEVICE_ID
     */
    public static function run(
        OtpDevicesClient $client,
        string $deviceId,
        array $headers = []
    ): void {
        $action = new self($client);

        try {
            $action->client->setResponse(
                $action->client->getClient()->request(
                    'DELETE',
                    "private/otp-devices/{$deviceId}",
                    $headers
                )
            );

            $client->handleWrappedResponse($action->handleResponse(...));
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $client->getTaler()->getLogger()->error("Taler delete OTP device request failed: {$e->getCode()}, {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Async variant
     *
     * @param OtpDevicesClient $client
     * @param string $deviceId
     * @param array<string, string> $headers
     * @return mixed
     */
    public static function runAsync(
        OtpDevicesClient $client,
        string $deviceId,
        array $headers = []
    ): mixed {
        $action = new self($client);

        return $client
            ->getClient()
            ->requestAsync('DELETE', "private/otp-devices/{$deviceId}", $headers)
            ->then(function (ResponseInterface $response) use ($action) {
                $action->client->setResponse($response);
                return $action->client->handleWrappedResponse($action->handleResponse(...));
            });
    }

    private function handleResponse(ResponseInterface $response): void
    {
        $this->client->parseResponseBody($response, 204);
    }
}



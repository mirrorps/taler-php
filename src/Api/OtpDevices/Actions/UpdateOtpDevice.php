<?php

namespace Taler\Api\OtpDevices\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\OtpDevices\Dto\OtpDevicePatchDetails;
use Taler\Api\OtpDevices\OtpDevicesClient;
use Taler\Exception\TalerException;

class UpdateOtpDevice
{
    public function __construct(
        private OtpDevicesClient $client
    ) {}

    /**
     * @param OtpDevicesClient $client
     * @param string $deviceId
     * @param OtpDevicePatchDetails $details
     * @param array<string, string> $headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     * @see https://docs.taler.net/core/api-merchant.html#patch-[-instances-$INSTANCE]-private-otp-devices-$DEVICE_ID
     */
    public static function run(
        OtpDevicesClient $client,
        string $deviceId,
        OtpDevicePatchDetails $details,
        array $headers = []
    ): void {
        $self = new self($client);

        try {
            $body = json_encode($details, JSON_THROW_ON_ERROR);

            $self->client->setResponse(
                $self->client->getClient()->request(
                    'PATCH',
                    "private/otp-devices/{$deviceId}",
                    $headers,
                    $body
                )
            );

            $client->handleWrappedResponse($self->handleResponse(...));
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $sanitized = \Taler\Helpers\sanitizeString((string) $e->getMessage());
            $client->getTaler()->getLogger()->error("Taler update OTP device request failed: {$e->getCode()}, {$sanitized}");
            throw $e;
        }
    }

    /**
     * @param OtpDevicesClient $client
     * @param string $deviceId
     * @param OtpDevicePatchDetails $details
     * @param array<string, string> $headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public static function runAsync(
        OtpDevicesClient $client,
        string $deviceId,
        OtpDevicePatchDetails $details,
        array $headers = []
    ): mixed {
        $self = new self($client);

        $body = json_encode($details, JSON_THROW_ON_ERROR);

        return $client
            ->getClient()
            ->requestAsync('PATCH', "private/otp-devices/{$deviceId}", $headers, $body)
            ->then(function (ResponseInterface $response) use ($self) {
                $self->client->setResponse($response);
                return $self->client->handleWrappedResponse($self->handleResponse(...));
            });
    }

    private function handleResponse(ResponseInterface $response): void
    {
        // Endpoint returns 204 No Content
        $this->client->parseResponseBody($response, 204);
    }
}




<?php

namespace Taler\Api\OtpDevices\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\OtpDevices\Dto\OtpDeviceAddDetails;
use Taler\Api\OtpDevices\OtpDevicesClient;
use Taler\Exception\TalerException;

class CreateOtpDevice
{
    public function __construct(
        private OtpDevicesClient $client
    ) {}

    /**
     * @param OtpDevicesClient $client
     * @param OtpDeviceAddDetails $details
     * @param array<string, string> $headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     * @see https://docs.taler.net/core/api-merchant.html#post-[-instances-$INSTANCE]-private-otp-devices
     */
    public static function run(
        OtpDevicesClient $client,
        OtpDeviceAddDetails $details,
        array $headers = []
    ): void {
        $self = new self($client);

        try {
            $body = json_encode($details, JSON_THROW_ON_ERROR);

            $self->client->setResponse(
                $self->client->getClient()->request(
                    'POST',
                    'private/otp-devices',
                    $headers,
                    $body
                )
            );

            $client->handleWrappedResponse($self->handleResponse(...));
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $sanitized = \Taler\Helpers\sanitizeString((string) $e->getMessage());
            $client->getTaler()->getLogger()->error("Taler create OTP device request failed: {$e->getCode()}, {$sanitized}");
            throw $e;
        }
    }

    /**
     * @param OtpDevicesClient $client
     * @param OtpDeviceAddDetails $details
     * @param array<string, string> $headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public static function runAsync(
        OtpDevicesClient $client,
        OtpDeviceAddDetails $details,
        array $headers = []
    ): mixed {
        $self = new self($client);

        $body = json_encode($details, JSON_THROW_ON_ERROR);

        return $client
            ->getClient()
            ->requestAsync('POST', 'private/otp-devices', $headers, $body)
            ->then(function (ResponseInterface $response) use ($self) {
                $self->client->setResponse($response);
                return $self->client->handleWrappedResponse($self->handleResponse(...));
            });
    }

    private function handleResponse(ResponseInterface $response): void
    {
        // Endpoint returns 204 No Content; parse will check status and return null
        $this->client->parseResponseBody($response, 204);
    }
}



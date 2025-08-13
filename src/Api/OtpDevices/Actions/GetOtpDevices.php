<?php

namespace Taler\Api\OtpDevices\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\OtpDevices\Dto\OtpDevicesSummaryResponse;
use Taler\Api\OtpDevices\OtpDevicesClient;
use Taler\Exception\TalerException;

class GetOtpDevices
{
    public function __construct(
        private OtpDevicesClient $client
    ) {}

    /**
     * @param OtpDevicesClient $client
     * @param array<string, string> $headers
     * @return OtpDevicesSummaryResponse|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     * @see https://docs.taler.net/core/api-merchant.html#get-[-instances-$INSTANCE]-private-otp-devices
     */
    public static function run(OtpDevicesClient $client, array $headers = []): OtpDevicesSummaryResponse|array
    {
        $self = new self($client);

        try {
            $self->client->setResponse(
                $self->client->getClient()->request('GET', 'private/otp-devices', $headers)
            );

            /** @var OtpDevicesSummaryResponse|array{otp_devices: array<int, array{otp_device_id: string, device_description: string}>} $result */
            $result = $client->handleWrappedResponse($self->handleResponse(...));
            return $result;
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $client->getTaler()->getLogger()->error("Taler get OTP devices request failed: {$e->getCode()}, {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * @param OtpDevicesClient $client
     * @param array<string, string> $headers
     * @return mixed
     */
    public static function runAsync(OtpDevicesClient $client, array $headers = []): mixed
    {
        $self = new self($client);

        return $client
            ->getClient()
            ->requestAsync('GET', 'private/otp-devices', $headers)
            ->then(function (ResponseInterface $response) use ($self) {
                $self->client->setResponse($response);
                return $self->client->handleWrappedResponse($self->handleResponse(...));
            });
    }

    private function handleResponse(ResponseInterface $response): OtpDevicesSummaryResponse
    {
        $data = $this->client->parseResponseBody($response, 200);
        return OtpDevicesSummaryResponse::createFromArray($data);
    }
}



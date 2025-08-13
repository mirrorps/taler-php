<?php

namespace Taler\Api\OtpDevices\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\OtpDevices\Dto\GetOtpDeviceRequest;
use Taler\Api\OtpDevices\Dto\OtpDeviceDetails;
use Taler\Api\OtpDevices\OtpDevicesClient;
use Taler\Exception\TalerException;

class GetOtpDevice
{
    public function __construct(
        private OtpDevicesClient $client
    ) {}

    /**
     * @param OtpDevicesClient $client
     * @param string $deviceId
     * @param GetOtpDeviceRequest|null $request Optional query parameters
     * @param array<string, string> $headers
     * @return OtpDeviceDetails|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     * @see https://docs.taler.net/core/api-merchant.html#get-[-instances-$INSTANCE]-private-otp-devices-$DEVICE_ID
     */
    public static function run(OtpDevicesClient $client, string $deviceId, ?GetOtpDeviceRequest $request = null, array $headers = []): OtpDeviceDetails|array
    {
        $self = new self($client);

        try {
            $uri = "private/otp-devices/{$deviceId}";
            
            $query = $request?->toArray() ?? [];
            if (!empty($query)) {
                $uri .= '?' . http_build_query($query);
            }
            $self->client->setResponse(
                $self->client->getClient()->request('GET', $uri, $headers)
            );

            /** @var OtpDeviceDetails|array<string, mixed> $result */
            $result = $client->handleWrappedResponse($self->handleResponse(...));
            return $result;
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $client->getTaler()->getLogger()->error("Taler get OTP device request failed: {$e->getCode()}, {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * @param OtpDevicesClient $client
     * @param string $deviceId
     * @param GetOtpDeviceRequest|null $request Optional query parameters
     * @param array<string, string> $headers
     * @return mixed
     */
    public static function runAsync(OtpDevicesClient $client, string $deviceId, ?GetOtpDeviceRequest $request = null, array $headers = []): mixed
    {
        $self = new self($client);

        $query = $request?->toArray() ?? [];
        $uri = "private/otp-devices/{$deviceId}";
        if ($query !== []) {
            $uri .= '?' . http_build_query($query);
        }

        return $client
            ->getClient()
            ->requestAsync('GET', $uri, $headers)
            ->then(function (ResponseInterface $response) use ($self) {
                $self->client->setResponse($response);
                return $self->client->handleWrappedResponse($self->handleResponse(...));
            });
    }

    private function handleResponse(ResponseInterface $response): OtpDeviceDetails
    {
        $data = $this->client->parseResponseBody($response, 200);
        return OtpDeviceDetails::createFromArray($data);
    }
}




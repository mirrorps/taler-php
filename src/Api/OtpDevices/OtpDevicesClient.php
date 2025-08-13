<?php

namespace Taler\Api\OtpDevices;

use Taler\Api\Base\AbstractApiClient;
use Taler\Api\OtpDevices\Dto\OtpDeviceAddDetails;
use Taler\Api\OtpDevices\Dto\OtpDevicePatchDetails;
use Taler\Exception\TalerException;

class OtpDevicesClient extends AbstractApiClient
{
    /**
     * @param OtpDeviceAddDetails $details
     * @param array<string, string> $headers Optional request headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     */
    public function createOtpDevice(OtpDeviceAddDetails $details, array $headers = []): void
    {
        Actions\CreateOtpDevice::run($this, $details, $headers);
    }

    /**
     * @param OtpDeviceAddDetails $details
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function createOtpDeviceAsync(OtpDeviceAddDetails $details, array $headers = []): mixed
    {
        return Actions\CreateOtpDevice::runAsync($this, $details, $headers);
    }

    /**
     * @param string $deviceId
     * @param OtpDevicePatchDetails $details
     * @param array<string, string> $headers Optional request headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     */
    public function updateOtpDevice(string $deviceId, OtpDevicePatchDetails $details, array $headers = []): void
    {
        Actions\UpdateOtpDevice::run($this, $deviceId, $details, $headers);
    }

    /**
     * @param string $deviceId
     * @param OtpDevicePatchDetails $details
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function updateOtpDeviceAsync(string $deviceId, OtpDevicePatchDetails $details, array $headers = []): mixed
    {
        return Actions\UpdateOtpDevice::runAsync($this, $deviceId, $details, $headers);
    }
}



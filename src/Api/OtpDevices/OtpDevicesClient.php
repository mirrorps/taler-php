<?php

namespace Taler\Api\OtpDevices;

use Taler\Api\Base\AbstractApiClient;
use Taler\Api\OtpDevices\Dto\OtpDeviceAddDetails;
use Taler\Api\OtpDevices\Dto\OtpDevicePatchDetails;
use Taler\Api\OtpDevices\Dto\GetOtpDeviceRequest;
use Taler\Api\OtpDevices\Dto\OtpDeviceDetails;
use Taler\Api\OtpDevices\Dto\OtpDevicesSummaryResponse;
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

    /**
     * @param array<string, string> $headers Optional request headers
     * @return OtpDevicesSummaryResponse|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public function getOtpDevices(array $headers = []): OtpDevicesSummaryResponse|array
    {
        return Actions\GetOtpDevices::run($this, $headers);
    }

    /**
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     */
    public function getOtpDevicesAsync(array $headers = []): mixed
    {
        return Actions\GetOtpDevices::runAsync($this, $headers);
    }

    /**
     * @param string $deviceId
     * @param array<string, string> $headers Optional request headers
     * @return OtpDeviceDetails|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public function getOtpDevice(string $deviceId, ?GetOtpDeviceRequest $request = null, array $headers = []): OtpDeviceDetails|array
    {
        return Actions\GetOtpDevice::run($this, $deviceId, $request, $headers);
    }

    /**
     * @param string $deviceId
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     */
    public function getOtpDeviceAsync(string $deviceId, ?GetOtpDeviceRequest $request = null, array $headers = []): mixed
    {
        return Actions\GetOtpDevice::runAsync($this, $deviceId, $request, $headers);
    }

    /**
     * @param string $deviceId
     * @param array<string, string> $headers Optional request headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     */
    public function deleteOtpDevice(string $deviceId, array $headers = []): void
    {
        Actions\DeleteOtpDevice::run($this, $deviceId, $headers);
    }

    /**
     * @param string $deviceId
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     */
    public function deleteOtpDeviceAsync(string $deviceId, array $headers = []): mixed
    {
        return Actions\DeleteOtpDevice::runAsync($this, $deviceId, $headers);
    }
}



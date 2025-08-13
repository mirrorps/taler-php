<?php

namespace Taler\Api\OtpDevices\Dto;

/**
 * DTO for the list of OTP devices response.
 *
 * Response shape per docs:
 * { "otp_devices": [ { otp_device_id, device_description }, ... ] }
 *
 * Do not include data validation (as requested).
 */
class OtpDevicesSummaryResponse
{
    /**
     * @param array<OtpDeviceEntry> $otp_devices
     */
    public function __construct(
        public readonly array $otp_devices,
    ) {
    }

    /**
     * @param array{otp_devices: array<int, array{otp_device_id: string, device_description: string}>} $data
     */
    public static function createFromArray(array $data): self
    {
        $devices = array_map(
            static fn(array $d) => OtpDeviceEntry::createFromArray($d),
            $data['otp_devices']
        );

        return new self($devices);
    }
}



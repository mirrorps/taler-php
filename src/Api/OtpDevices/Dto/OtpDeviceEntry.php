<?php

namespace Taler\Api\OtpDevices\Dto;

/**
 * Represents a single OTP device entry returned by the Merchant API.
 *
 * @see https://docs.taler.net/core/api-merchant.html#get-[-instances-$INSTANCE]-private-otp-devices
 */
class OtpDeviceEntry
{
    public function __construct(
        public readonly string $otp_device_id,
        public readonly string $device_description,
    ) {
    }

    /**
     * @param array{otp_device_id: string, device_description: string} $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            otp_device_id: $data['otp_device_id'],
            device_description: $data['device_description'],
        );
    }
}



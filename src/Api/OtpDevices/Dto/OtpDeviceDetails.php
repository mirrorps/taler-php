<?php

namespace Taler\Api\OtpDevices\Dto;

/**
 * Detailed OTP device DTO returned by GET private/otp-devices/$DEVICE_ID.
 * Do not include data validation.
 *
 * Expected response per docs:
 * {
 *   "device_description": string,
 *   "otp_algorithm": string|int,
 *   "otp_ctr"?: int,
 *   "otp_timestamp": { t_s: int|string },
 *   "otp_code"?: string
 * }
 */
class OtpDeviceDetails
{
    public function __construct(
        public readonly string $device_description,
        public readonly int|string $otp_algorithm,
        public readonly int $otp_timestamp,
        public readonly ?int $otp_ctr = null,
        public readonly ?string $otp_code = null,
    ) {
    }

    /**
     * @param array{
     *   device_description: string,
     *   otp_algorithm: string|int,
     *   otp_ctr?: int|null,
     *   otp_timestamp: int,
     *   otp_code?: string|null
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            device_description: $data['device_description'],
            otp_algorithm: $data['otp_algorithm'],
            otp_ctr: $data['otp_ctr'] ?? null,
            otp_timestamp: (int) $data['otp_timestamp'],
            otp_code: $data['otp_code'] ?? null,
        );
    }
}





<?php

namespace Taler\Api\OtpDevices\Dto;

/**
 * Response DTO for OTP device update.
 * The spec for PATCH returns 204 No Content, but we keep this DTO for future-proofing/tests.
 * Do not include data validation as requested.
 */
class OtpDeviceUpdateResponse
{
    /**
     * @param string|null $otp_device_id
     * @param string|null $otp_device_description
     * @param int|string|null $otp_algorithm
     * @param int|null $otp_ctr
     */
    public function __construct(
        public readonly ?string $otp_device_id = null,
        public readonly ?string $otp_device_description = null,
        public readonly int|string|null $otp_algorithm = null,
        public readonly ?int $otp_ctr = null
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            otp_device_id: $data['otp_device_id'] ?? null,
            otp_device_description: $data['otp_device_description'] ?? null,
            otp_algorithm: $data['otp_algorithm'] ?? null,
            otp_ctr: $data['otp_ctr'] ?? null
        );
    }
}




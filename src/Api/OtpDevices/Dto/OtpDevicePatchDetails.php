<?php

namespace Taler\Api\OtpDevices\Dto;

/**
 * DTO for updating an OTP device.
 *
 * Docs: https://docs.taler.net/core/api-merchant.html#patch-[-instances-$INSTANCE]-private-otp-devices-$DEVICE_ID
 */
class OtpDevicePatchDetails
{
    /**
     * @param string|null $otp_device_description Human-readable description
     * @param string|null $otp_key Base32-encoded shared secret (RFC 3548)
     * @param int|string|null $otp_algorithm Algorithm for computing POS confirmation
     * @param int|null $otp_ctr Optional counter value
     * @param bool $validate Whether to validate inputs
     */
    public function __construct(
        public readonly ?string $otp_device_description = null,
        public readonly ?string $otp_key = null,
        public readonly int|string|null $otp_algorithm = null,
        public readonly ?int $otp_ctr = null,
        bool $validate = true
    ) {
        if ($validate) {
            $this->validate();
        }
    }

    /**
     * @param array{
     *   otp_device_description?: string|null,
     *   otp_key?: string|null,
     *   otp_algorithm?: string|int|null,
     *   otp_ctr?: int|null
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            otp_device_description: $data['otp_device_description'] ?? null,
            otp_key: $data['otp_key'] ?? null,
            otp_algorithm: $data['otp_algorithm'] ?? null,
            otp_ctr: $data['otp_ctr'] ?? null
        );
    }

    public function validate(): void
    {
        if ($this->otp_algorithm !== null) {
            $validStringAlgorithms = ['NONE', 'TOTP_WITHOUT_PRICE', 'TOTP_WITH_PRICE'];
            $validIntAlgorithms = [0, 1, 2];

            if (is_string($this->otp_algorithm)) {
                $normalized = strtoupper($this->otp_algorithm);
                if (!in_array($normalized, $validStringAlgorithms, true)) {
                    throw new \InvalidArgumentException('otp_algorithm must be one of: NONE, TOTP_WITHOUT_PRICE, TOTP_WITH_PRICE or 0,1,2');
                }
            } elseif (is_int($this->otp_algorithm)) {
                if (!in_array($this->otp_algorithm, $validIntAlgorithms, true)) {
                    throw new \InvalidArgumentException('otp_algorithm must be one of: 0, 1, 2 or NONE, TOTP_WITHOUT_PRICE, TOTP_WITH_PRICE');
                }
            } else {
                throw new \InvalidArgumentException('otp_algorithm must be an integer or string when provided');
            }
        }
    }
}




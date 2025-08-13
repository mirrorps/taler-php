<?php

namespace Taler\Api\OtpDevices\Dto;

/**
 * DTO for creating an OTP device.
 *
 * See Merchant API: OTP Devices - Create device
 * Docs: https://docs.taler.net/core/api-merchant.html#post-[-instances-$INSTANCE]-private-otp-devices
 */
class OtpDeviceAddDetails
{
    /**
     * @param string $otp_device_id Unique identifier for the OTP device
     * @param string $otp_device_description Human-readable description
     * @param string $otp_key Base32-encoded shared secret
     * @param int|string $otp_algorithm Algorithm for computing the POS confirmation
     *  - Integer or string: 0|"NONE", 1|"TOTP_WITHOUT_PRICE", 2|"TOTP_WITH_PRICE"
     * @param int|null $otp_ctr Optional counter value (kept for future compatibility)
     * @param bool $validate Whether to validate inputs
     */
    public function __construct(
        public readonly string $otp_device_id,
        public readonly string $otp_device_description,
        public readonly string $otp_key,
        public readonly int|string $otp_algorithm,
        public readonly ?int $otp_ctr = null,
        bool $validate = true
    ) {
        if ($validate) {
            $this->validate();
        }
    }

    /**
     * @param array{
     *     otp_device_id: string,
     *     otp_device_description: string,
     *     otp_key: string,
     *     otp_algorithm: string,
     *     otp_ctr?: int|null
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            otp_device_id: $data['otp_device_id'],
            otp_device_description: $data['otp_device_description'],
            otp_key: $data['otp_key'],
            otp_algorithm: $data['otp_algorithm'],
            otp_ctr: $data['otp_ctr'] ?? null
        );
    }

    public function validate(): void
    {
        if ($this->otp_device_id === '') {
            throw new \InvalidArgumentException('otp_device_id must not be empty');
        }

        if ($this->otp_device_description === '') {
            throw new \InvalidArgumentException('otp_device_description must not be empty');
        }

        if ($this->otp_key === '') {
            throw new \InvalidArgumentException('otp_key must not be empty');
        }

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
            throw new \InvalidArgumentException('otp_algorithm must be an integer or string');
        }
    }
}



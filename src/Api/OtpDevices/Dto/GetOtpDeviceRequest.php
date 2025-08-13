<?php

namespace Taler\Api\OtpDevices\Dto;

/**
 * Request DTO for querying a single OTP device
 *
 * Supported query parameters (protocol v10+):
 * - faketime: Optional timestamp in seconds used to compute current OTP code
 * - price: Optional amount (e.g., "EUR:1.23") used to compute current OTP code
 */
class GetOtpDeviceRequest
{
    public function __construct(
        public readonly ?int $faketime = null,
        public readonly ?string $price = null,
    ) {}

    /**
     * @return array<string, string|int>
     */
    public function toArray(): array
    {
        $params = [];
        if ($this->faketime !== null) {
            $params['faketime'] = $this->faketime;
        }
        if ($this->price !== null) {
            $params['price'] = $this->price;
        }
        return $params;
    }
}




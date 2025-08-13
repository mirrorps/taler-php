<?php

namespace Taler\Tests\Api\OtpDevices\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\OtpDevices\Dto\OtpDevicePatchDetails;

class OtpDevicePatchDetailsTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = [
            'otp_device_description' => 'Office TOTP',
            'otp_key' => 'JBSWY3DPEHPK3PXP',
            'otp_algorithm' => 'TOTP_WITHOUT_PRICE',
            'otp_ctr' => 1,
        ];

        $dto = OtpDevicePatchDetails::createFromArray($data);

        $this->assertSame('Office TOTP', $dto->otp_device_description);
        $this->assertSame('JBSWY3DPEHPK3PXP', $dto->otp_key);
        $this->assertSame('TOTP_WITHOUT_PRICE', $dto->otp_algorithm);
        $this->assertSame(1, $dto->otp_ctr);
    }

    public function testValidationInvalidAlgorithm(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new OtpDevicePatchDetails(
            otp_device_description: 'x',
            otp_key: null,
            otp_algorithm: 'invalid',
            otp_ctr: null
        );
    }

    public function testOptionalFields(): void
    {
        $dto = new OtpDevicePatchDetails(otp_device_description: 'desc');

        $this->assertSame('desc', $dto->otp_device_description);
        $this->assertNull($dto->otp_key);
        $this->assertNull($dto->otp_algorithm);
        $this->assertNull($dto->otp_ctr);
    }
}




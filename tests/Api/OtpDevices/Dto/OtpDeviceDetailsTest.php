<?php

namespace Taler\Tests\Api\OtpDevices\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\OtpDevices\Dto\OtpDeviceDetails;

class OtpDeviceDetailsTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = [
            'device_description' => 'Front desk POS',
            'otp_algorithm' => 'TOTP_WITH_PRICE',
            'otp_ctr' => 10,
            'otp_timestamp' => 1700000002,
            'otp_code' => '654321',
        ];

        $dto = OtpDeviceDetails::createFromArray($data);

        $this->assertSame('Front desk POS', $dto->device_description);
        $this->assertSame('TOTP_WITH_PRICE', $dto->otp_algorithm);
        $this->assertSame(10, $dto->otp_ctr);
        $this->assertSame(1700000002, $dto->otp_timestamp);
        $this->assertSame('654321', $dto->otp_code);
    }

    public function testCreateFromArrayWithMinimalFields(): void
    {
        $data = [
            'device_description' => 'Side POS',
            'otp_algorithm' => 1,
            'otp_timestamp' => 1700000100,
        ];

        $dto = OtpDeviceDetails::createFromArray($data);
        $this->assertSame('Side POS', $dto->device_description);
        $this->assertSame(1, $dto->otp_algorithm);
        $this->assertNull($dto->otp_ctr);
        $this->assertNull($dto->otp_code);
        $this->assertSame(1700000100, $dto->otp_timestamp);
    }
}

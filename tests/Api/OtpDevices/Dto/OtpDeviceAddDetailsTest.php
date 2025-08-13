<?php

namespace Taler\Tests\Api\OtpDevices\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\OtpDevices\Dto\OtpDeviceAddDetails;

class OtpDeviceAddDetailsTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = [
            'otp_device_id' => 'dev-1',
            'otp_device_description' => 'Office TOTP',
            'otp_key' => 'JBSWY3DPEHPK3PXP',
            'otp_algorithm' => 'TOTP_WITHOUT_PRICE',
        ];

        $dto = OtpDeviceAddDetails::createFromArray($data);

        $this->assertSame('dev-1', $dto->otp_device_id);
        $this->assertSame('Office TOTP', $dto->otp_device_description);
        $this->assertSame('JBSWY3DPEHPK3PXP', $dto->otp_key);
        $this->assertSame('TOTP_WITHOUT_PRICE', $dto->otp_algorithm);
        $this->assertNull($dto->otp_ctr);
    }

    public function testInvalidAlgorithmString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new OtpDeviceAddDetails(
            otp_device_id: 'dev-2',
            otp_device_description: 'Invalid algo',
            otp_key: 'JBSWY3DPEHPK3PXP',
            otp_algorithm: 'hotp'
        );
    }

    public function testValidIntAndStringAlgorithms(): void
    {
        $dto = new OtpDeviceAddDetails(
            otp_device_id: 'dev-3',
            otp_device_description: 'No price',
            otp_key: 'JBSWY3DPEHPK3PXP',
            otp_algorithm: 1
        );

        $this->assertSame(1, $dto->otp_algorithm);

        $dto2 = new OtpDeviceAddDetails(
            otp_device_id: 'dev-4',
            otp_device_description: 'With price',
            otp_key: 'JBSWY3DPEHPK3PXP',
            otp_algorithm: 'TOTP_WITH_PRICE'
        );
        $this->assertSame('TOTP_WITH_PRICE', $dto2->otp_algorithm);
    }
}



<?php

namespace Taler\Tests\Api\OtpDevices\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\OtpDevices\Dto\OtpDeviceUpdateResponse;

class OtpDeviceUpdateResponseTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = [
            'otp_device_id' => 'dev-1',
            'otp_device_description' => 'Updated desc',
            'otp_algorithm' => 'TOTP_WITH_PRICE',
            'otp_ctr' => 10,
        ];

        $dto = OtpDeviceUpdateResponse::createFromArray($data);

        $this->assertSame('dev-1', $dto->otp_device_id);
        $this->assertSame('Updated desc', $dto->otp_device_description);
        $this->assertSame('TOTP_WITH_PRICE', $dto->otp_algorithm);
        $this->assertSame(10, $dto->otp_ctr);
    }
}




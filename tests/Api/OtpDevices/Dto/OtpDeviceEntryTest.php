<?php

namespace Taler\Tests\Api\OtpDevices\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\OtpDevices\Dto\OtpDeviceEntry;

class OtpDeviceEntryTest extends TestCase
{
    public function testFromArray(): void
    {
        $data = [
            'otp_device_id' => 'device1',
            'device_description' => 'Front desk POS',
        ];

        $dto = OtpDeviceEntry::createFromArray($data);
        $this->assertSame('device1', $dto->otp_device_id);
        $this->assertSame('Front desk POS', $dto->device_description);
    }
}



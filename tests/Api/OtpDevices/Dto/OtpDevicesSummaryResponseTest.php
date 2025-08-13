<?php

namespace Taler\Tests\Api\OtpDevices\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\OtpDevices\Dto\OtpDevicesSummaryResponse;

class OtpDevicesSummaryResponseTest extends TestCase
{
    public function testFromArray(): void
    {
        $data = [
            'otp_devices' => [
                [
                    'otp_device_id' => 'device1',
                    'device_description' => 'Front desk POS',
                ],
                [
                    'otp_device_id' => 'device2',
                    'device_description' => 'Side counter POS',
                ],
            ],
        ];

        $dto = OtpDevicesSummaryResponse::createFromArray($data);
        $this->assertCount(2, $dto->otp_devices);
        $this->assertSame('device1', $dto->otp_devices[0]->otp_device_id);
        $this->assertSame('Front desk POS', $dto->otp_devices[0]->device_description);
    }
}



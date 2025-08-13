<?php

namespace Taler\Tests\Api\OtpDevices\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\OtpDevices\Dto\GetOtpDeviceRequest;

class GetOtpDeviceRequestTest extends TestCase
{
    public function testToArrayWithAll(): void
    {
        $req = new GetOtpDeviceRequest(faketime: 1700000000, price: 'EUR:1.23');
        $arr = $req->toArray();

        $this->assertSame(1700000000, $arr['faketime']);
        $this->assertSame('EUR:1.23', $arr['price']);
    }

    public function testToArrayMinimal(): void
    {
        $req = new GetOtpDeviceRequest();
        $this->assertSame([], $req->toArray());
    }
}




<?php

namespace Taler\Tests\Api\Instance\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Instance\Dto\GetKycStatusRequest;

class GetKycStatusRequestTest extends TestCase
{
    public function testToArrayWithAllParams(): void
    {
        $req = new GetKycStatusRequest(
            h_wire: 'hash123',
            exchange_url: 'https://exchange.example.com',
            lpt: 3,
            timeout_ms: 5000
        );

        $arr = $req->toArray();

        $this->assertSame('hash123', $arr['h_wire']);
        $this->assertSame('https://exchange.example.com', $arr['exchange_url']);
        $this->assertSame(3, $arr['lpt']);
        $this->assertSame(5000, $arr['timeout_ms']);
    }

    public function testValidationInvalidLpt(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new GetKycStatusRequest(lpt: 4);
    }

    public function testValidationInvalidTimeout(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new GetKycStatusRequest(timeout_ms: 0);
    }
}




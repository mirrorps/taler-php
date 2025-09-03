<?php

namespace Taler\Tests\Api\Instance\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Instance\Dto\QueryInstancesResponse;

class QueryInstancesResponseTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = [
            'name' => 'Shop A',
            'merchant_pub' => 'ABCD1234',
            'address' => ['country' => 'DE', 'town' => 'Berlin'],
            'jurisdiction' => ['country' => 'DE', 'town' => 'Berlin'],
            'use_stefan' => true,
            'default_wire_transfer_delay' => ['d_us' => 86400000000],
            'default_pay_delay' => ['d_us' => 3600000000],
            'auth' => ['method' => 'token'],
            'email' => 'a@example',
            'email_validated' => true,
            'phone_number' => '+491234',
            'phone_validated' => false,
            'website' => 'https://a.example',
            'logo' => 'data:image/png;base64,abc'
        ];

        $result = QueryInstancesResponse::createFromArray($data);
        $this->assertInstanceOf(QueryInstancesResponse::class, $result);
        $this->assertSame('Shop A', $result->name);
        $this->assertSame('ABCD1234', $result->merchant_pub);
        $this->assertSame(['method' => 'token'], $result->auth);
        $this->assertTrue($result->use_stefan);
    }
}



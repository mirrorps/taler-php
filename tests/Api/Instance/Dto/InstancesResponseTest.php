<?php

namespace Taler\Tests\Api\Instance\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Instance\Dto\InstancesResponse;
use Taler\Api\Instance\Dto\Instance;

class InstancesResponseTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = [
            'instances' => [
                [
                    'name' => 'Shop A',
                    'id' => 'shop-a',
                    'merchant_pub' => 'ABCD1234',
                    'payment_targets' => ['default'],
                    'deleted' => false,
                    'website' => 'https://a.example',
                    'logo' => 'data:image/png;base64,abc'
                ],
                [
                    'name' => 'Shop B',
                    'id' => 'shop-b',
                    'merchant_pub' => 'XYZ987',
                    'payment_targets' => ['pos', 'online'],
                    'deleted' => true
                ]
            ]
        ];

        $result = InstancesResponse::createFromArray($data);
        $this->assertInstanceOf(InstancesResponse::class, $result);
        $this->assertCount(2, $result->instances);
        $this->assertInstanceOf(Instance::class, $result->instances[0]);
        $this->assertSame('Shop A', $result->instances[0]->name);
        $this->assertSame('shop-b', $result->instances[1]->id);
    }
}



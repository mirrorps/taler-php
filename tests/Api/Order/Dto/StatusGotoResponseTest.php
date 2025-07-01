<?php

namespace Taler\Tests\Api\Order\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Order\Dto\StatusGotoResponse;

class StatusGotoResponseTest extends TestCase
{
    public function testFromArray(): void
    {
        $data = [
            'public_reorder_url' => 'https://shop.test.taler.net/reorder/123'
        ];

        $response = StatusGotoResponse::fromArray($data);

        $this->assertInstanceOf(StatusGotoResponse::class, $response);
        $this->assertEquals($data['public_reorder_url'], $response->public_reorder_url);
    }
} 
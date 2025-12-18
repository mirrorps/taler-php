<?php

namespace Taler\Tests\Api\Wallet\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Wallet\Dto\StatusGotoResponse;

class StatusGotoResponseTest extends TestCase
{
    public function testFromArray(): void
    {
        $data = [
            'public_reorder_url' => 'https://shop.test.taler.net/reorder/123'
        ];

        $response = StatusGotoResponse::createFromArray($data);

        $this->assertInstanceOf(StatusGotoResponse::class, $response);
        $this->assertEquals($data['public_reorder_url'], $response->public_reorder_url);
    }
} 
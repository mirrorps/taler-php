<?php

namespace Taler\Tests\Api\Order\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Order\Dto\StatusUnpaidResponse;

class StatusUnpaidResponseTest extends TestCase
{
    public function testFromArray(): void
    {
        $data = [
            'taler_pay_uri' => 'taler://pay/example.com/orders/123',
            'fulfillment_url' => 'https://shop.test.taler.net/order/123/status',
            'already_paid_order_id' => 'order_456'
        ];

        $response = StatusUnpaidResponse::fromArray($data);

        $this->assertInstanceOf(StatusUnpaidResponse::class, $response);
        $this->assertEquals($data['taler_pay_uri'], $response->taler_pay_uri);
        $this->assertEquals($data['fulfillment_url'], $response->fulfillment_url);
        $this->assertEquals($data['already_paid_order_id'], $response->already_paid_order_id);
    }

    public function testFromArrayWithoutOptionalFields(): void
    {
        $data = [
            'taler_pay_uri' => 'taler://pay/example.com/orders/123'
        ];

        $response = StatusUnpaidResponse::fromArray($data);

        $this->assertInstanceOf(StatusUnpaidResponse::class, $response);
        $this->assertEquals($data['taler_pay_uri'], $response->taler_pay_uri);
        $this->assertNull($response->fulfillment_url);
        $this->assertNull($response->already_paid_order_id);
    }
} 
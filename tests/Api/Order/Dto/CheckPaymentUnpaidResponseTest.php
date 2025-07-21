<?php

namespace Taler\Tests\Api\Order\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\Timestamp;
use Taler\Api\Order\Dto\CheckPaymentUnpaidResponse;

class CheckPaymentUnpaidResponseTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = [
            'order_status' => 'unpaid',
            'taler_pay_uri' => 'taler://pay/example.com/1234',
            'creation_time' => [
                't_s' => 123456789
            ],
            'summary' => 'Test Order Summary',
            'total_amount' => '10.00',
            'already_paid_order_id' => 'prev-order-123',
            'already_paid_fulfillment_url' => 'http://example.com/fulfill/prev-order-123',
            'order_status_url' => 'http://example.com/status/1234'
        ];

        $response = CheckPaymentUnpaidResponse::createFromArray($data);

        $this->assertInstanceOf(CheckPaymentUnpaidResponse::class, $response);
        $this->assertEquals('unpaid', $response->order_status);
        $this->assertEquals('taler://pay/example.com/1234', $response->taler_pay_uri);
        $this->assertInstanceOf(Timestamp::class, $response->creation_time);
        $this->assertEquals(123456789, $response->creation_time->t_s);
        $this->assertEquals('Test Order Summary', $response->summary);
        $this->assertEquals('10.00', $response->total_amount);
        $this->assertEquals('prev-order-123', $response->already_paid_order_id);
        $this->assertEquals('http://example.com/fulfill/prev-order-123', $response->already_paid_fulfillment_url);
        $this->assertEquals('http://example.com/status/1234', $response->order_status_url);
    }

    public function testCreateFromArrayWithOptionalFieldsOmitted(): void
    {
        $data = [
            'order_status' => 'unpaid',
            'taler_pay_uri' => 'taler://pay/example.com/1234',
            'creation_time' => [
                't_s' => 123456789
            ],
            'summary' => 'Test Order Summary',
            'order_status_url' => 'http://example.com/status/1234'
        ];

        $response = CheckPaymentUnpaidResponse::createFromArray($data);

        $this->assertInstanceOf(CheckPaymentUnpaidResponse::class, $response);
        $this->assertEquals('unpaid', $response->order_status);
        $this->assertEquals('taler://pay/example.com/1234', $response->taler_pay_uri);
        $this->assertInstanceOf(Timestamp::class, $response->creation_time);
        $this->assertEquals(123456789, $response->creation_time->t_s);
        $this->assertEquals('Test Order Summary', $response->summary);
        $this->assertNull($response->total_amount);
        $this->assertNull($response->already_paid_order_id);
        $this->assertNull($response->already_paid_fulfillment_url);
        $this->assertEquals('http://example.com/status/1234', $response->order_status_url);
    }
} 
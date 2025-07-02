<?php

namespace Taler\Tests\Api\Wallet\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Wallet\Dto\StatusPaidResponse;

class StatusPaidResponseTest extends TestCase
{
    public function testFromArray(): void
    {
        $data = [
            'refunded' => true,
            'refund_pending' => true,
            'refund_amount' => '100.00',
            'refund_taken' => '50.00'
        ];

        $response = StatusPaidResponse::fromArray($data);

        $this->assertInstanceOf(StatusPaidResponse::class, $response);
        $this->assertEquals($data['refunded'], $response->refunded);
        $this->assertEquals($data['refund_pending'], $response->refund_pending);
        $this->assertEquals($data['refund_amount'], $response->refund_amount);
        $this->assertEquals($data['refund_taken'], $response->refund_taken);
    }

    public function testFromArrayWithFalseValues(): void
    {
        $data = [
            'refunded' => false,
            'refund_pending' => false,
            'refund_amount' => '0.00',
            'refund_taken' => '0.00'
        ];

        $response = StatusPaidResponse::fromArray($data);

        $this->assertInstanceOf(StatusPaidResponse::class, $response);
        $this->assertFalse($response->refunded);
        $this->assertFalse($response->refund_pending);
        $this->assertEquals($data['refund_amount'], $response->refund_amount);
        $this->assertEquals($data['refund_taken'], $response->refund_taken);
    }
} 
<?php

namespace Taler\Tests\Api\Order\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Order\Dto\RefundDetails;
use Taler\Api\Dto\Timestamp;
use Taler\Api\Order\Dto\Amount;

class RefundDetailsTest extends TestCase
{
    public function testFromArray(): void
    {
        $data = [
            'reason' => 'Customer dissatisfaction',
            'pending' => true,
            'timestamp' => ['t_s' => 1234567890],
            'amount' => 'EUR:25.50'
        ];

        $refund = RefundDetails::createFromArray($data);

        $this->assertInstanceOf(RefundDetails::class, $refund);
        $this->assertEquals($data['reason'], $refund->reason);
        $this->assertEquals($data['pending'], $refund->pending);
        $this->assertInstanceOf(Timestamp::class, $refund->timestamp);
        $this->assertEquals($data['timestamp']['t_s'], $refund->timestamp->t_s);
        $this->assertInstanceOf(Amount::class, $refund->amount);
        $this->assertEquals('EUR:25.50', (string) $refund->amount);
    }
} 
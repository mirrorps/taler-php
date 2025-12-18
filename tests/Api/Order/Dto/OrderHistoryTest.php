<?php

namespace Taler\Tests\Api\Order\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\Timestamp;
use Taler\Api\Order\Dto\OrderHistory;
use Taler\Api\Order\Dto\OrderHistoryEntry;

class OrderHistoryTest extends TestCase
{
    private const SAMPLE_ORDER_ID = 'test-123';
    private const SAMPLE_ROW_ID = 42;
    private const SAMPLE_TIMESTAMP_S = 1234567890;
    private const SAMPLE_AMOUNT = '10.00';
    private const SAMPLE_SUMMARY = 'Test order';

    public function testConstruct(): void
    {
        $timestamp = new Timestamp(self::SAMPLE_TIMESTAMP_S);
        $orderEntry = new OrderHistoryEntry(
            order_id: self::SAMPLE_ORDER_ID,
            row_id: self::SAMPLE_ROW_ID,
            timestamp: $timestamp,
            amount: self::SAMPLE_AMOUNT,
            summary: self::SAMPLE_SUMMARY,
            refundable: true,
            paid: false
        );

        $orderHistory = new OrderHistory(
            orders: [$orderEntry]
        );

        $this->assertCount(1, $orderHistory->orders);
        $this->assertSame($orderEntry, $orderHistory->orders[0]);
    }

    public function testFromArrayWithValidData(): void
    {
        $data = [
            'orders' => [
                [
                    'order_id' => self::SAMPLE_ORDER_ID,
                    'row_id' => self::SAMPLE_ROW_ID,
                    'timestamp' => ['t_s' => self::SAMPLE_TIMESTAMP_S],
                    'amount' => self::SAMPLE_AMOUNT,
                    'summary' => self::SAMPLE_SUMMARY,
                    'refundable' => true,
                    'paid' => false
                ]
            ]
        ];

        $orderHistory = OrderHistory::createFromArray($data);

        $this->assertCount(1, $orderHistory->orders);
        $this->assertInstanceOf(OrderHistoryEntry::class, $orderHistory->orders[0]);
        $this->assertSame(self::SAMPLE_ORDER_ID, $orderHistory->orders[0]->order_id);
        $this->assertSame(self::SAMPLE_ROW_ID, $orderHistory->orders[0]->row_id);
        $this->assertInstanceOf(Timestamp::class, $orderHistory->orders[0]->timestamp);
        $this->assertSame(self::SAMPLE_TIMESTAMP_S, $orderHistory->orders[0]->timestamp->t_s);
        $this->assertSame(self::SAMPLE_AMOUNT, $orderHistory->orders[0]->amount);
        $this->assertSame(self::SAMPLE_SUMMARY, $orderHistory->orders[0]->summary);
        $this->assertTrue($orderHistory->orders[0]->refundable);
        $this->assertFalse($orderHistory->orders[0]->paid);
    }
} 
<?php

namespace Taler\Tests\Api\Order\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\Timestamp;
use Taler\Api\Order\Dto\OrderHistoryEntry;

class OrderHistoryEntryTest extends TestCase
{
    private const SAMPLE_ORDER_ID = 'test-123';
    private const SAMPLE_ROW_ID = 42;
    private const SAMPLE_TIMESTAMP_S = 1234567890;
    private const SAMPLE_AMOUNT = '10.00';
    private const SAMPLE_SUMMARY = 'Test order';

    public function testConstruct(): void
    {
        $timestamp = new Timestamp(self::SAMPLE_TIMESTAMP_S);

        $orderHistoryEntry = new OrderHistoryEntry(
            order_id: self::SAMPLE_ORDER_ID,
            row_id: self::SAMPLE_ROW_ID,
            timestamp: $timestamp,
            amount: self::SAMPLE_AMOUNT,
            summary: self::SAMPLE_SUMMARY,
            refundable: true,
            paid: false
        );

        $this->assertSame(self::SAMPLE_ORDER_ID, $orderHistoryEntry->order_id);
        $this->assertSame(self::SAMPLE_ROW_ID, $orderHistoryEntry->row_id);
        $this->assertSame($timestamp, $orderHistoryEntry->timestamp);
        $this->assertSame(self::SAMPLE_AMOUNT, $orderHistoryEntry->amount);
        $this->assertSame(self::SAMPLE_SUMMARY, $orderHistoryEntry->summary);
        $this->assertTrue($orderHistoryEntry->refundable);
        $this->assertFalse($orderHistoryEntry->paid);
    }

    public function testFromArrayWithValidData(): void
    {
        $data = [
            'order_id' => self::SAMPLE_ORDER_ID,
            'row_id' => self::SAMPLE_ROW_ID,
            'timestamp' => ['t_s' => self::SAMPLE_TIMESTAMP_S],
            'amount' => self::SAMPLE_AMOUNT,
            'summary' => self::SAMPLE_SUMMARY,
            'refundable' => true,
            'paid' => false
        ];

        $orderHistoryEntry = OrderHistoryEntry::fromArray($data);

        $this->assertSame(self::SAMPLE_ORDER_ID, $orderHistoryEntry->order_id);
        $this->assertSame(self::SAMPLE_ROW_ID, $orderHistoryEntry->row_id);
        $this->assertInstanceOf(Timestamp::class, $orderHistoryEntry->timestamp);
        $this->assertSame(self::SAMPLE_TIMESTAMP_S, $orderHistoryEntry->timestamp->t_s);
        $this->assertSame(self::SAMPLE_AMOUNT, $orderHistoryEntry->amount);
        $this->assertSame(self::SAMPLE_SUMMARY, $orderHistoryEntry->summary);
        $this->assertTrue($orderHistoryEntry->refundable);
        $this->assertFalse($orderHistoryEntry->paid);
    }
} 
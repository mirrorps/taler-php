<?php

declare(strict_types=1);

namespace Taler\Tests\Api\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\AccountLimit;
use Taler\Api\Dto\RelativeTime;

class AccountLimitTest extends TestCase
{
    private const SAMPLE_OPERATION_TYPE = 'WITHDRAW';
    private const SAMPLE_THRESHOLD = 'TALER:100.00';

    public function testConstructorWithValidData(): void
    {
        $timeframe = new RelativeTime(3600000000); // 1 hour in microseconds
        $softLimit = true;

        $accountLimit = new AccountLimit(
            operation_type: self::SAMPLE_OPERATION_TYPE,
            timeframe: $timeframe,
            threshold: self::SAMPLE_THRESHOLD,
            soft_limit: $softLimit
        );

        $this->assertSame(self::SAMPLE_OPERATION_TYPE, $accountLimit->operation_type);
        $this->assertSame($timeframe, $accountLimit->timeframe);
        $this->assertSame(self::SAMPLE_THRESHOLD, $accountLimit->threshold);
        $this->assertSame($softLimit, $accountLimit->soft_limit);
    }

    public function testConstructorWithDefaultSoftLimit(): void
    {
        $timeframe = new RelativeTime(3600000000); // 1 hour in microseconds

        $accountLimit = new AccountLimit(
            operation_type: self::SAMPLE_OPERATION_TYPE,
            timeframe: $timeframe,
            threshold: self::SAMPLE_THRESHOLD
        );

        $this->assertSame(self::SAMPLE_OPERATION_TYPE, $accountLimit->operation_type);
        $this->assertSame($timeframe, $accountLimit->timeframe);
        $this->assertSame(self::SAMPLE_THRESHOLD, $accountLimit->threshold);
        $this->assertFalse($accountLimit->soft_limit);
    }

    public function testConstructorWithInvalidOperationType(): void
    {
        $timeframe = new RelativeTime(3600000000); // 1 hour in microseconds

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid operation type "INVALID". Must be one of: WITHDRAW, DEPOSIT, MERGE, BALANCE, CLOSE, AGGREGATE, TRANSACTION, REFUND');

        new AccountLimit(
            operation_type: 'INVALID',
            timeframe: $timeframe,
            threshold: self::SAMPLE_THRESHOLD
        );
    }

    public function testFromArrayWithValidData(): void
    {
        $data = [
            'operation_type' => self::SAMPLE_OPERATION_TYPE,
            'timeframe' => ['d_us' => 3600000000], // 1 hour in microseconds
            'threshold' => self::SAMPLE_THRESHOLD,
            'soft_limit' => true
        ];

        $accountLimit = AccountLimit::fromArray($data);

        $this->assertSame(self::SAMPLE_OPERATION_TYPE, $accountLimit->operation_type);
        $this->assertSame(3600000000, $accountLimit->timeframe->d_us);
        $this->assertSame(self::SAMPLE_THRESHOLD, $accountLimit->threshold);
        $this->assertTrue($accountLimit->soft_limit);
    }

    public function testFromArrayWithDefaultSoftLimit(): void
    {
        $data = [
            'operation_type' => self::SAMPLE_OPERATION_TYPE,
            'timeframe' => ['d_us' => 3600000000], // 1 hour in microseconds
            'threshold' => self::SAMPLE_THRESHOLD
        ];

        $accountLimit = AccountLimit::fromArray($data);

        $this->assertSame(self::SAMPLE_OPERATION_TYPE, $accountLimit->operation_type);
        $this->assertSame(3600000000, $accountLimit->timeframe->d_us);
        $this->assertSame(self::SAMPLE_THRESHOLD, $accountLimit->threshold);
        $this->assertFalse($accountLimit->soft_limit);
    }
} 
<?php

namespace Taler\Tests\Api\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\GlobalFees;
use Taler\Api\Dto\RelativeTime;
use Taler\Api\Dto\Timestamp;

class GlobalFeesTest extends TestCase
{
    private const SAMPLE_START_DATE_S = 1710979200; // 2024-03-20T00:00:00Z in seconds
    private const SAMPLE_END_DATE_S = 1711065600; // 2024-03-21T00:00:00Z in seconds
    private const SAMPLE_HISTORY_FEE = 'TALER:0.50';
    private const SAMPLE_ACCOUNT_FEE = 'TALER:10.00';
    private const SAMPLE_PURSE_FEE = 'TALER:5.00';
    private const SAMPLE_MASTER_SIG = 'ED25519-SIG-123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    public function testConstructorWithValidData(): void
    {
        $startDate = new Timestamp(self::SAMPLE_START_DATE_S);
        $endDate = new Timestamp(self::SAMPLE_END_DATE_S);
        $historyExpiration = new RelativeTime(3600000000); // 1 hour in microseconds
        $purseTimeout = new RelativeTime(7200000000); // 2 hours in microseconds
        $purseAccountLimit = 5;

        $globalFees = new GlobalFees(
            start_date: $startDate,
            end_date: $endDate,
            history_fee: self::SAMPLE_HISTORY_FEE,
            account_fee: self::SAMPLE_ACCOUNT_FEE,
            purse_fee: self::SAMPLE_PURSE_FEE,
            history_expiration: $historyExpiration,
            purse_account_limit: $purseAccountLimit,
            purse_timeout: $purseTimeout,
            master_sig: self::SAMPLE_MASTER_SIG
        );

        $this->assertSame($startDate, $globalFees->start_date);
        $this->assertSame($endDate, $globalFees->end_date);
        $this->assertSame(self::SAMPLE_HISTORY_FEE, $globalFees->history_fee);
        $this->assertSame(self::SAMPLE_ACCOUNT_FEE, $globalFees->account_fee);
        $this->assertSame(self::SAMPLE_PURSE_FEE, $globalFees->purse_fee);
        $this->assertSame($historyExpiration, $globalFees->history_expiration);
        $this->assertSame($purseAccountLimit, $globalFees->purse_account_limit);
        $this->assertSame($purseTimeout, $globalFees->purse_timeout);
        $this->assertSame(self::SAMPLE_MASTER_SIG, $globalFees->master_sig);
    }

    public function testConstructorWithNegativePurseAccountLimit(): void
    {
        $startDate = new Timestamp(self::SAMPLE_START_DATE_S);
        $endDate = new Timestamp(self::SAMPLE_END_DATE_S);
        $historyExpiration = new RelativeTime(3600000000); // 1 hour in microseconds
        $purseTimeout = new RelativeTime(7200000000); // 2 hours in microseconds

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('purse_account_limit must be non-negative');

        new GlobalFees(
            start_date: $startDate,
            end_date: $endDate,
            history_fee: self::SAMPLE_HISTORY_FEE,
            account_fee: self::SAMPLE_ACCOUNT_FEE,
            purse_fee: self::SAMPLE_PURSE_FEE,
            history_expiration: $historyExpiration,
            purse_account_limit: -1,
            purse_timeout: $purseTimeout,
            master_sig: self::SAMPLE_MASTER_SIG
        );
    }

    public function testFromArrayWithValidData(): void
    {
        $data = [
            'start_date' => ['t_s' => self::SAMPLE_START_DATE_S],
            'end_date' => ['t_s' => self::SAMPLE_END_DATE_S],
            'history_fee' => self::SAMPLE_HISTORY_FEE,
            'account_fee' => self::SAMPLE_ACCOUNT_FEE,
            'purse_fee' => self::SAMPLE_PURSE_FEE,
            'history_expiration' => ['d_us' => 3600000000], // 1 hour in microseconds
            'purse_account_limit' => 5,
            'purse_timeout' => ['d_us' => 7200000000], // 2 hours in microseconds
            'master_sig' => self::SAMPLE_MASTER_SIG
        ];

        $globalFees = GlobalFees::createFromArray($data);

        $this->assertInstanceOf(Timestamp::class, $globalFees->start_date);
        $this->assertSame(self::SAMPLE_START_DATE_S, $globalFees->start_date->t_s);
        $this->assertInstanceOf(Timestamp::class, $globalFees->end_date);
        $this->assertSame(self::SAMPLE_END_DATE_S, $globalFees->end_date->t_s);
        $this->assertSame(self::SAMPLE_HISTORY_FEE, $globalFees->history_fee);
        $this->assertSame(self::SAMPLE_ACCOUNT_FEE, $globalFees->account_fee);
        $this->assertSame(self::SAMPLE_PURSE_FEE, $globalFees->purse_fee);
        $this->assertSame(3600000000, $globalFees->history_expiration->d_us);
        $this->assertSame(5, $globalFees->purse_account_limit);
        $this->assertSame(7200000000, $globalFees->purse_timeout->d_us);
        $this->assertSame(self::SAMPLE_MASTER_SIG, $globalFees->master_sig);
    }
} 
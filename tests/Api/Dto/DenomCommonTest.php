<?php

namespace Taler\Tests\Api\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\DenomCommon;
use Taler\Api\Dto\Timestamp;

class DenomCommonTest extends TestCase
{
    private const SAMPLE_MASTER_SIG = 'EDDSAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';
    private const SAMPLE_STAMP_START_S = 1710979200; // 2024-03-20T00:00:00Z in seconds
    private const SAMPLE_STAMP_EXPIRE_WITHDRAW_S = 1711065600; // 2024-03-21T00:00:00Z in seconds
    private const SAMPLE_STAMP_EXPIRE_DEPOSIT_S = 1711152000; // 2024-03-22T00:00:00Z in seconds
    private const SAMPLE_STAMP_EXPIRE_LEGAL_S = 1711238400; // 2024-03-23T00:00:00Z in seconds

    public function testConstructWithValidData(): void
    {
        $stampStart = new Timestamp(self::SAMPLE_STAMP_START_S);
        $stampExpireWithdraw = new Timestamp(self::SAMPLE_STAMP_EXPIRE_WITHDRAW_S);
        $stampExpireDeposit = new Timestamp(self::SAMPLE_STAMP_EXPIRE_DEPOSIT_S);
        $stampExpireLegal = new Timestamp(self::SAMPLE_STAMP_EXPIRE_LEGAL_S);

        $denomCommon = new DenomCommon(
            master_sig: self::SAMPLE_MASTER_SIG,
            stamp_start: $stampStart,
            stamp_expire_withdraw: $stampExpireWithdraw,
            stamp_expire_deposit: $stampExpireDeposit,
            stamp_expire_legal: $stampExpireLegal,
            lost: false
        );

        $this->assertSame(self::SAMPLE_MASTER_SIG, $denomCommon->master_sig);
        $this->assertSame($stampStart, $denomCommon->stamp_start);
        $this->assertSame($stampExpireWithdraw, $denomCommon->stamp_expire_withdraw);
        $this->assertSame($stampExpireDeposit, $denomCommon->stamp_expire_deposit);
        $this->assertSame($stampExpireLegal, $denomCommon->stamp_expire_legal);
        $this->assertFalse($denomCommon->lost);
    }

    public function testConstructWithoutLostField(): void
    {
        $stampStart = new Timestamp(self::SAMPLE_STAMP_START_S);
        $stampExpireWithdraw = new Timestamp(self::SAMPLE_STAMP_EXPIRE_WITHDRAW_S);
        $stampExpireDeposit = new Timestamp(self::SAMPLE_STAMP_EXPIRE_DEPOSIT_S);
        $stampExpireLegal = new Timestamp(self::SAMPLE_STAMP_EXPIRE_LEGAL_S);

        $denomCommon = new DenomCommon(
            master_sig: self::SAMPLE_MASTER_SIG,
            stamp_start: $stampStart,
            stamp_expire_withdraw: $stampExpireWithdraw,
            stamp_expire_deposit: $stampExpireDeposit,
            stamp_expire_legal: $stampExpireLegal
        );

        $this->assertSame(self::SAMPLE_MASTER_SIG, $denomCommon->master_sig);
        $this->assertSame($stampStart, $denomCommon->stamp_start);
        $this->assertSame($stampExpireWithdraw, $denomCommon->stamp_expire_withdraw);
        $this->assertSame($stampExpireDeposit, $denomCommon->stamp_expire_deposit);
        $this->assertSame($stampExpireLegal, $denomCommon->stamp_expire_legal);
        $this->assertNull($denomCommon->lost);
    }

    public function testConstructWithLostTrue(): void
    {
        $stampStart = new Timestamp(self::SAMPLE_STAMP_START_S);
        $stampExpireWithdraw = new Timestamp(self::SAMPLE_STAMP_EXPIRE_WITHDRAW_S);
        $stampExpireDeposit = new Timestamp(self::SAMPLE_STAMP_EXPIRE_DEPOSIT_S);
        $stampExpireLegal = new Timestamp(self::SAMPLE_STAMP_EXPIRE_LEGAL_S);

        $denomCommon = new DenomCommon(
            master_sig: self::SAMPLE_MASTER_SIG,
            stamp_start: $stampStart,
            stamp_expire_withdraw: $stampExpireWithdraw,
            stamp_expire_deposit: $stampExpireDeposit,
            stamp_expire_legal: $stampExpireLegal,
            lost: true
        );

        $this->assertTrue($denomCommon->lost);
    }

    public function testFromArrayWithValidData(): void
    {
        $data = [
            'master_sig' => self::SAMPLE_MASTER_SIG,
            'stamp_start' => ['t_s' => self::SAMPLE_STAMP_START_S],
            'stamp_expire_withdraw' => ['t_s' => self::SAMPLE_STAMP_EXPIRE_WITHDRAW_S],
            'stamp_expire_deposit' => ['t_s' => self::SAMPLE_STAMP_EXPIRE_DEPOSIT_S],
            'stamp_expire_legal' => ['t_s' => self::SAMPLE_STAMP_EXPIRE_LEGAL_S],
            'lost' => false
        ];

        $denomCommon = DenomCommon::createFromArray($data);

        $this->assertSame(self::SAMPLE_MASTER_SIG, $denomCommon->master_sig);
        $this->assertInstanceOf(Timestamp::class, $denomCommon->stamp_start);
        $this->assertSame(self::SAMPLE_STAMP_START_S, $denomCommon->stamp_start->t_s);
        $this->assertInstanceOf(Timestamp::class, $denomCommon->stamp_expire_withdraw);
        $this->assertSame(self::SAMPLE_STAMP_EXPIRE_WITHDRAW_S, $denomCommon->stamp_expire_withdraw->t_s);
        $this->assertInstanceOf(Timestamp::class, $denomCommon->stamp_expire_deposit);
        $this->assertSame(self::SAMPLE_STAMP_EXPIRE_DEPOSIT_S, $denomCommon->stamp_expire_deposit->t_s);
        $this->assertInstanceOf(Timestamp::class, $denomCommon->stamp_expire_legal);
        $this->assertSame(self::SAMPLE_STAMP_EXPIRE_LEGAL_S, $denomCommon->stamp_expire_legal->t_s);
        $this->assertFalse($denomCommon->lost);
    }

    public function testFromArrayWithoutLostField(): void
    {
        $data = [
            'master_sig' => self::SAMPLE_MASTER_SIG,
            'stamp_start' => ['t_s' => self::SAMPLE_STAMP_START_S],
            'stamp_expire_withdraw' => ['t_s' => self::SAMPLE_STAMP_EXPIRE_WITHDRAW_S],
            'stamp_expire_deposit' => ['t_s' => self::SAMPLE_STAMP_EXPIRE_DEPOSIT_S],
            'stamp_expire_legal' => ['t_s' => self::SAMPLE_STAMP_EXPIRE_LEGAL_S]
        ];

        $denomCommon = DenomCommon::createFromArray($data);

        $this->assertSame(self::SAMPLE_MASTER_SIG, $denomCommon->master_sig);
        $this->assertInstanceOf(Timestamp::class, $denomCommon->stamp_start);
        $this->assertSame(self::SAMPLE_STAMP_START_S, $denomCommon->stamp_start->t_s);
        $this->assertInstanceOf(Timestamp::class, $denomCommon->stamp_expire_withdraw);
        $this->assertSame(self::SAMPLE_STAMP_EXPIRE_WITHDRAW_S, $denomCommon->stamp_expire_withdraw->t_s);
        $this->assertInstanceOf(Timestamp::class, $denomCommon->stamp_expire_deposit);
        $this->assertSame(self::SAMPLE_STAMP_EXPIRE_DEPOSIT_S, $denomCommon->stamp_expire_deposit->t_s);
        $this->assertInstanceOf(Timestamp::class, $denomCommon->stamp_expire_legal);
        $this->assertSame(self::SAMPLE_STAMP_EXPIRE_LEGAL_S, $denomCommon->stamp_expire_legal->t_s);
        $this->assertNull($denomCommon->lost);
    }

    public function testFromArrayWithLostTrue(): void
    {
        $data = [
            'master_sig' => self::SAMPLE_MASTER_SIG,
            'stamp_start' => ['t_s' => self::SAMPLE_STAMP_START_S],
            'stamp_expire_withdraw' => ['t_s' => self::SAMPLE_STAMP_EXPIRE_WITHDRAW_S],
            'stamp_expire_deposit' => ['t_s' => self::SAMPLE_STAMP_EXPIRE_DEPOSIT_S],
            'stamp_expire_legal' => ['t_s' => self::SAMPLE_STAMP_EXPIRE_LEGAL_S],
            'lost' => true
        ];

        $denomCommon = DenomCommon::createFromArray($data);

        $this->assertTrue($denomCommon->lost);
    }

    public function testFromArrayWithTimestampStrings(): void
    {
        $data = [
            'master_sig' => self::SAMPLE_MASTER_SIG,
            'stamp_start' => ['t_s' => 'never'],
            'stamp_expire_withdraw' => ['t_s' => self::SAMPLE_STAMP_EXPIRE_WITHDRAW_S],
            'stamp_expire_deposit' => ['t_s' => self::SAMPLE_STAMP_EXPIRE_DEPOSIT_S],
            'stamp_expire_legal' => ['t_s' => self::SAMPLE_STAMP_EXPIRE_LEGAL_S]
        ];

        $denomCommon = DenomCommon::createFromArray($data);

        $this->assertInstanceOf(Timestamp::class, $denomCommon->stamp_start);
        $this->assertSame('never', $denomCommon->stamp_start->t_s);
    }
} 
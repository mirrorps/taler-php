<?php

namespace Taler\Tests\Api\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Contract\DenomGroupCommon;
use Taler\Api\Dto\AbstractDenomGroup;
use Taler\Api\Dto\Timestamp;

class AbstractDenomGroupTest extends TestCase
{
    private const SAMPLE_VALUE = 'TALER:10.00';
    private const SAMPLE_FEE_WITHDRAW = 'TALER:0.50';
    private const SAMPLE_FEE_DEPOSIT = 'TALER:0.25';
    private const SAMPLE_FEE_REFRESH = 'TALER:0.15';
    private const SAMPLE_FEE_REFUND = 'TALER:0.10';
    private const SAMPLE_MASTER_SIG = 'EDDSAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';
    private const SAMPLE_START_TIME = 1716153600;
    private const SAMPLE_EXPIRE_WITHDRAW = 1716240000;
    private const SAMPLE_EXPIRE_DEPOSIT = 1716326400;
    private const SAMPLE_EXPIRE_LEGAL = 1716412800;

    /** @var array{
     *     master_sig: string,
     *     stamp_start: Timestamp,
     *     stamp_expire_withdraw: Timestamp,
     *     stamp_expire_deposit: Timestamp,
     *     stamp_expire_legal: Timestamp,
     *     lost?: bool
     * }
     */
    private array $sampleDenom;

    protected function setUp(): void
    {
        $this->sampleDenom = [
            'master_sig' => self::SAMPLE_MASTER_SIG,
            'stamp_start' => new Timestamp(self::SAMPLE_START_TIME),
            'stamp_expire_withdraw' => new Timestamp(self::SAMPLE_EXPIRE_WITHDRAW),
            'stamp_expire_deposit' => new Timestamp(self::SAMPLE_EXPIRE_DEPOSIT),
            'stamp_expire_legal' => new Timestamp(self::SAMPLE_EXPIRE_LEGAL)
        ];
    }

    public function testGetters(): void
    {
        $group = new class(
            self::SAMPLE_VALUE,
            self::SAMPLE_FEE_WITHDRAW,
            self::SAMPLE_FEE_DEPOSIT,
            self::SAMPLE_FEE_REFRESH,
            self::SAMPLE_FEE_REFUND,
            [$this->sampleDenom]
        ) extends AbstractDenomGroup {
            public function getCipher(): string
            {
                return 'TEST';
            }

            public static function fromArray(array $data): DenomGroupCommon
            {
                return new self(
                    value: $data['value'],
                    fee_withdraw: $data['fee_withdraw'],
                    fee_deposit: $data['fee_deposit'],
                    fee_refresh: $data['fee_refresh'],
                    fee_refund: $data['fee_refund'],
                    denoms: $data['denoms']
                );
            }
        };

        $this->assertSame(self::SAMPLE_VALUE, $group->getValue());
        $this->assertSame(self::SAMPLE_FEE_WITHDRAW, $group->getFeeWithdraw());
        $this->assertSame(self::SAMPLE_FEE_DEPOSIT, $group->getFeeDeposit());
        $this->assertSame(self::SAMPLE_FEE_REFRESH, $group->getFeeRefresh());
        $this->assertSame(self::SAMPLE_FEE_REFUND, $group->getFeeRefund());
        $this->assertSame('TEST', $group->getCipher());
        $this->assertSame([$this->sampleDenom], $group->getDenoms());
    }

    public function testObjectImmutability(): void
    {
        $group = new class(
            self::SAMPLE_VALUE,
            self::SAMPLE_FEE_WITHDRAW,
            self::SAMPLE_FEE_DEPOSIT,
            self::SAMPLE_FEE_REFRESH,
            self::SAMPLE_FEE_REFUND,
            [$this->sampleDenom]
        ) extends AbstractDenomGroup {
            public function getCipher(): string
            {
                return 'TEST';
            }

            public static function fromArray(array $data): DenomGroupCommon
            {
                return new self(
                    value: $data['value'],
                    fee_withdraw: $data['fee_withdraw'],
                    fee_deposit: $data['fee_deposit'],
                    fee_refresh: $data['fee_refresh'],
                    fee_refund: $data['fee_refund'],
                    denoms: $data['denoms']
                );
            }
        };

        $this->assertTrue((new \ReflectionProperty($group, 'value'))->isReadOnly());
        $this->assertTrue((new \ReflectionProperty($group, 'fee_withdraw'))->isReadOnly());
        $this->assertTrue((new \ReflectionProperty($group, 'fee_deposit'))->isReadOnly());
        $this->assertTrue((new \ReflectionProperty($group, 'fee_refresh'))->isReadOnly());
        $this->assertTrue((new \ReflectionProperty($group, 'fee_refund'))->isReadOnly());
        $this->assertTrue((new \ReflectionProperty($group, 'denoms'))->isReadOnly());
    }
} 
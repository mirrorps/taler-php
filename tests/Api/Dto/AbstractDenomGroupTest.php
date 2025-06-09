<?php

namespace Tests\Api\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Contract\DenomGroupCommon;
use Taler\Api\Dto\AbstractDenomGroup;

class AbstractDenomGroupTest extends TestCase
{
    private const SAMPLE_VALUE = 'TALER:10.00';
    private const SAMPLE_FEE_WITHDRAW = 'TALER:0.50';
    private const SAMPLE_FEE_DEPOSIT = 'TALER:0.25';
    private const SAMPLE_FEE_REFRESH = 'TALER:0.15';
    private const SAMPLE_FEE_REFUND = 'TALER:0.10';
    private const SAMPLE_MASTER_SIG = 'EDDSAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';
    private const SAMPLE_START_TIME = '2024-03-20T00:00:00Z';
    private const SAMPLE_EXPIRE_WITHDRAW = '2024-03-21T00:00:00Z';
    private const SAMPLE_EXPIRE_DEPOSIT = '2024-03-22T00:00:00Z';
    private const SAMPLE_EXPIRE_LEGAL = '2024-03-23T00:00:00Z';

    /** @var array{
     *     master_sig: string,
     *     stamp_start: string,
     *     stamp_expire_withdraw: string,
     *     stamp_expire_deposit: string,
     *     stamp_expire_legal: string,
     *     lost?: bool
     * }
     */
    private array $sampleDenom;

    protected function setUp(): void
    {
        $this->sampleDenom = [
            'master_sig' => self::SAMPLE_MASTER_SIG,
            'stamp_start' => self::SAMPLE_START_TIME,
            'stamp_expire_withdraw' => self::SAMPLE_EXPIRE_WITHDRAW,
            'stamp_expire_deposit' => self::SAMPLE_EXPIRE_DEPOSIT,
            'stamp_expire_legal' => self::SAMPLE_EXPIRE_LEGAL
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
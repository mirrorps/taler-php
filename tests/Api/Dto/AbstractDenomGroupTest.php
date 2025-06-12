<?php

namespace Taler\Tests\Api\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Contract\DenomGroupCommonContract;
use Taler\Api\Dto\AbstractDenomGroup;

class AbstractDenomGroupTest extends TestCase
{
    private const SAMPLE_VALUE = 'TALER:10.00';
    private const SAMPLE_FEE_WITHDRAW = 'TALER:0.50';
    private const SAMPLE_FEE_DEPOSIT = 'TALER:0.25';
    private const SAMPLE_FEE_REFRESH = 'TALER:0.15';
    private const SAMPLE_FEE_REFUND = 'TALER:0.10';

    public function testGetters(): void
    {
        $group = new class(
            self::SAMPLE_VALUE,
            self::SAMPLE_FEE_WITHDRAW,
            self::SAMPLE_FEE_DEPOSIT,
            self::SAMPLE_FEE_REFRESH,
            self::SAMPLE_FEE_REFUND
        ) extends AbstractDenomGroup {
            public function getCipher(): string
            {
                return 'TEST';
            }

            public function getDenoms(): array
            {
                return [];
            }

            public static function fromArray(array $data): DenomGroupCommonContract
            {
                return new self(
                    value: $data['value'],
                    fee_withdraw: $data['fee_withdraw'],
                    fee_deposit: $data['fee_deposit'],
                    fee_refresh: $data['fee_refresh'],
                    fee_refund: $data['fee_refund']
                );
            }
        };

        // Test the getters defined in AbstractDenomGroup
        $this->assertSame(self::SAMPLE_VALUE, $group->getValue());
        $this->assertSame(self::SAMPLE_FEE_WITHDRAW, $group->getFeeWithdraw());
        $this->assertSame(self::SAMPLE_FEE_DEPOSIT, $group->getFeeDeposit());
        $this->assertSame(self::SAMPLE_FEE_REFRESH, $group->getFeeRefresh());
        $this->assertSame(self::SAMPLE_FEE_REFUND, $group->getFeeRefund());
    }

    public function testObjectImmutability(): void
    {
        $group = new class(
            self::SAMPLE_VALUE,
            self::SAMPLE_FEE_WITHDRAW,
            self::SAMPLE_FEE_DEPOSIT,
            self::SAMPLE_FEE_REFRESH,
            self::SAMPLE_FEE_REFUND
        ) extends AbstractDenomGroup {
            public function getCipher(): string
            {
                return 'TEST';
            }

            public function getDenoms(): array
            {
                return [];
            }

            public static function fromArray(array $data): DenomGroupCommonContract
            {
                return new self(
                    value: $data['value'],
                    fee_withdraw: $data['fee_withdraw'],
                    fee_deposit: $data['fee_deposit'],
                    fee_refresh: $data['fee_refresh'],
                    fee_refund: $data['fee_refund']
                );
            }
        };

        // Test that the properties defined in AbstractDenomGroup are readonly
        $this->assertTrue((new \ReflectionProperty($group, 'value'))->isReadOnly());
        $this->assertTrue((new \ReflectionProperty($group, 'fee_withdraw'))->isReadOnly());
        $this->assertTrue((new \ReflectionProperty($group, 'fee_deposit'))->isReadOnly());
        $this->assertTrue((new \ReflectionProperty($group, 'fee_refresh'))->isReadOnly());
        $this->assertTrue((new \ReflectionProperty($group, 'fee_refund'))->isReadOnly());
    }
} 
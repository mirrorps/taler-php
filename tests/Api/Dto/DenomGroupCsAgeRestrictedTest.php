<?php

namespace Tests\Api\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\DenomGroupCsAgeRestricted;

class DenomGroupCsAgeRestrictedTest extends TestCase
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
    private const SAMPLE_CS_PUB = 'CS25519-PUB-123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private const SAMPLE_AGE_MASK = '0000000000000000000000000000000000000000000000000000000000000001';

    /** @var array{
     *     value: string,
     *     fee_withdraw: string,
     *     fee_deposit: string,
     *     fee_refresh: string,
     *     fee_refund: string,
     *     cipher: string,
     *     age_mask: string,
     *     denoms: array<int, array{
     *         master_sig: string,
     *         stamp_start: string,
     *         stamp_expire_withdraw: string,
     *         stamp_expire_deposit: string,
     *         stamp_expire_legal: string,
     *         cs_pub: string,
     *         lost?: bool
     *     }>
     * }
     */
    private array $validData;

    protected function setUp(): void
    {
        $this->validData = [
            'value' => self::SAMPLE_VALUE,
            'fee_withdraw' => self::SAMPLE_FEE_WITHDRAW,
            'fee_deposit' => self::SAMPLE_FEE_DEPOSIT,
            'fee_refresh' => self::SAMPLE_FEE_REFRESH,
            'fee_refund' => self::SAMPLE_FEE_REFUND,
            'cipher' => 'CS+age_restricted',
            'age_mask' => self::SAMPLE_AGE_MASK,
            'denoms' => [
                [
                    'master_sig' => self::SAMPLE_MASTER_SIG,
                    'stamp_start' => self::SAMPLE_START_TIME,
                    'stamp_expire_withdraw' => self::SAMPLE_EXPIRE_WITHDRAW,
                    'stamp_expire_deposit' => self::SAMPLE_EXPIRE_DEPOSIT,
                    'stamp_expire_legal' => self::SAMPLE_EXPIRE_LEGAL,
                    'cs_pub' => self::SAMPLE_CS_PUB
                ]
            ]
        ];
    }

    public function testConstructWithValidData(): void
    {
        $group = new DenomGroupCsAgeRestricted(
            value: self::SAMPLE_VALUE,
            fee_withdraw: self::SAMPLE_FEE_WITHDRAW,
            fee_deposit: self::SAMPLE_FEE_DEPOSIT,
            fee_refresh: self::SAMPLE_FEE_REFRESH,
            fee_refund: self::SAMPLE_FEE_REFUND,
            age_mask: self::SAMPLE_AGE_MASK,
            denoms: $this->validData['denoms']
        );

        $this->assertSame(self::SAMPLE_VALUE, $group->getValue());
        $this->assertSame(self::SAMPLE_FEE_WITHDRAW, $group->getFeeWithdraw());
        $this->assertSame(self::SAMPLE_FEE_DEPOSIT, $group->getFeeDeposit());
        $this->assertSame(self::SAMPLE_FEE_REFRESH, $group->getFeeRefresh());
        $this->assertSame(self::SAMPLE_FEE_REFUND, $group->getFeeRefund());
        $this->assertSame('CS+age_restricted', $group->getCipher());
        $this->assertSame(self::SAMPLE_AGE_MASK, $group->getAgeMask());
        $this->assertSame($this->validData['denoms'], $group->getDenoms());
    }

    public function testFromArrayWithValidData(): void
    {
        $group = DenomGroupCsAgeRestricted::fromArray($this->validData);

        $this->assertSame(self::SAMPLE_VALUE, $group->getValue());
        $this->assertSame(self::SAMPLE_FEE_WITHDRAW, $group->getFeeWithdraw());
        $this->assertSame(self::SAMPLE_FEE_DEPOSIT, $group->getFeeDeposit());
        $this->assertSame(self::SAMPLE_FEE_REFRESH, $group->getFeeRefresh());
        $this->assertSame(self::SAMPLE_FEE_REFUND, $group->getFeeRefund());
        $this->assertSame('CS+age_restricted', $group->getCipher());
        $this->assertSame(self::SAMPLE_AGE_MASK, $group->getAgeMask());
        $this->assertSame($this->validData['denoms'], $group->getDenoms());
    }

    public function testFromArrayWithInvalidCipher(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid cipher type "INVALID". Expected "CS+age_restricted"');

        $data = $this->validData;
        $data['cipher'] = 'INVALID';
        DenomGroupCsAgeRestricted::fromArray($data);
    }

    public function testFromArrayWithLostDenom(): void
    {
        $data = $this->validData;
        $data['denoms'][0]['lost'] = true;

        $group = DenomGroupCsAgeRestricted::fromArray($data);
        $denoms = $group->getDenoms();
        $this->assertArrayHasKey('lost', $denoms[0]);
        $this->assertTrue($denoms[0]['lost'] ?? false);
    }
} 
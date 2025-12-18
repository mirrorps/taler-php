<?php

namespace Taler\Tests\Api\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\DenomCommon;
use Taler\Api\Dto\DenomGroupRsaAgeRestricted;
use Taler\Api\Dto\Timestamp;

class DenomGroupRsaAgeRestrictedTest extends TestCase
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
    private const SAMPLE_RSA_PUB = 'RSA-PUB-123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
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
     *         stamp_start: array{t_s: int|string},
     *         stamp_expire_withdraw: array{t_s: int|string},
     *         stamp_expire_deposit: array{t_s: int|string},
     *         stamp_expire_legal: array{t_s: int|string},
     *         rsa_pub: string,
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
            'cipher' => 'RSA+age_restricted',
            'age_mask' => self::SAMPLE_AGE_MASK,
            'denoms' => [
                [
                    'master_sig' => self::SAMPLE_MASTER_SIG,
                    'stamp_start' => ['t_s' => self::SAMPLE_START_TIME],
                    'stamp_expire_withdraw' => ['t_s' => self::SAMPLE_EXPIRE_WITHDRAW],
                    'stamp_expire_deposit' => ['t_s' => self::SAMPLE_EXPIRE_DEPOSIT],
                    'stamp_expire_legal' => ['t_s' => self::SAMPLE_EXPIRE_LEGAL],
                    'rsa_pub' => self::SAMPLE_RSA_PUB
                ]
            ]
        ];
    }

    public function testConstructWithValidData(): void
    {
        $denoms = array_map(
            fn(array $denom) => new DenomCommon(
                master_sig: $denom['master_sig'],
                stamp_start: new Timestamp($denom['stamp_start']['t_s']),
                stamp_expire_withdraw: new Timestamp($denom['stamp_expire_withdraw']['t_s']),
                stamp_expire_deposit: new Timestamp($denom['stamp_expire_deposit']['t_s']),
                stamp_expire_legal: new Timestamp($denom['stamp_expire_legal']['t_s']),
                lost: $denom['lost'] ?? null
            ),
            $this->validData['denoms']
        );

        $group = new DenomGroupRsaAgeRestricted(
            value: self::SAMPLE_VALUE,
            fee_withdraw: self::SAMPLE_FEE_WITHDRAW,
            fee_deposit: self::SAMPLE_FEE_DEPOSIT,
            fee_refresh: self::SAMPLE_FEE_REFRESH,
            fee_refund: self::SAMPLE_FEE_REFUND,
            denoms: $denoms,
            age_mask: self::SAMPLE_AGE_MASK,
        );

        $this->assertSame(self::SAMPLE_VALUE, $group->getValue());
        $this->assertSame(self::SAMPLE_FEE_WITHDRAW, $group->getFeeWithdraw());
        $this->assertSame(self::SAMPLE_FEE_DEPOSIT, $group->getFeeDeposit());
        $this->assertSame(self::SAMPLE_FEE_REFRESH, $group->getFeeRefresh());
        $this->assertSame(self::SAMPLE_FEE_REFUND, $group->getFeeRefund());
        $this->assertSame('RSA+age_restricted', $group->getCipher());
        $this->assertSame(self::SAMPLE_AGE_MASK, $group->getAgeMask());
        $this->assertEquals($denoms, $group->getDenoms());
    }

    public function testFromArrayWithValidData(): void
    {
        $group = DenomGroupRsaAgeRestricted::createFromArray($this->validData);

        $denoms = array_map(
            fn(array $denom) => new DenomCommon(
                master_sig: $denom['master_sig'],
                stamp_start: new Timestamp($denom['stamp_start']['t_s']),
                stamp_expire_withdraw: new Timestamp($denom['stamp_expire_withdraw']['t_s']),
                stamp_expire_deposit: new Timestamp($denom['stamp_expire_deposit']['t_s']),
                stamp_expire_legal: new Timestamp($denom['stamp_expire_legal']['t_s']),
                lost: $denom['lost'] ?? null
            ),
            $this->validData['denoms']
        );

        $this->assertSame(self::SAMPLE_VALUE, $group->getValue());
        $this->assertSame(self::SAMPLE_FEE_WITHDRAW, $group->getFeeWithdraw());
        $this->assertSame(self::SAMPLE_FEE_DEPOSIT, $group->getFeeDeposit());
        $this->assertSame(self::SAMPLE_FEE_REFRESH, $group->getFeeRefresh());
        $this->assertSame(self::SAMPLE_FEE_REFUND, $group->getFeeRefund());
        $this->assertSame('RSA+age_restricted', $group->getCipher());
        $this->assertSame(self::SAMPLE_AGE_MASK, $group->getAgeMask());
        $this->assertEquals($denoms, $group->getDenoms());
    }

    public function testFromArrayWithInvalidCipher(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid cipher type "INVALID". Expected "RSA+age_restricted"');

        $data = $this->validData;
        $data['cipher'] = 'INVALID';
        DenomGroupRsaAgeRestricted::createFromArray($data);
    }

    public function testFromArrayWithLostDenom(): void
    {
        $data = $this->validData;
        $data['denoms'][0]['lost'] = true;

        $group = DenomGroupRsaAgeRestricted::createFromArray($data);
        $denoms = $group->getDenoms();
        $this->assertTrue($denoms[0]->lost ?? false);
    }
} 
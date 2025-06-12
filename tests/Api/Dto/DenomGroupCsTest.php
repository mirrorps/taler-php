<?php

namespace Taler\Tests\Api\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\DenomCommon;
use Taler\Api\Dto\DenomGroupCs;

class DenomGroupCsTest extends TestCase
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
    private const SAMPLE_CS_PUB = 'CS25519-PUB-123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /** @var array{
     *     value: string,
     *     fee_withdraw: string,
     *     fee_deposit: string,
     *     fee_refresh: string,
     *     fee_refund: string,
     *     cipher: string,
     *     denoms: array<int, array{
     *         master_sig: string,
     *         stamp_start: array{t_s: int|string},
     *         stamp_expire_withdraw: array{t_s: int|string},
     *         stamp_expire_deposit: array{t_s: int|string},
     *         stamp_expire_legal: array{t_s: int|string},
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
            'cipher' => 'CS',
            'denoms' => [
                [
                    'master_sig' => self::SAMPLE_MASTER_SIG,
                    'stamp_start' => ['t_s' => self::SAMPLE_START_TIME],
                    'stamp_expire_withdraw' => ['t_s' => self::SAMPLE_EXPIRE_WITHDRAW],
                    'stamp_expire_deposit' => ['t_s' => self::SAMPLE_EXPIRE_DEPOSIT],
                    'stamp_expire_legal' => ['t_s' => self::SAMPLE_EXPIRE_LEGAL],
                    'cs_pub' => self::SAMPLE_CS_PUB
                ]
            ]
        ];
    }

    public function testConstructWithValidData(): void
    {
        $group = new DenomGroupCs(
            value: self::SAMPLE_VALUE,
            fee_withdraw: self::SAMPLE_FEE_WITHDRAW,
            fee_deposit: self::SAMPLE_FEE_DEPOSIT,
            fee_refresh: self::SAMPLE_FEE_REFRESH,
            fee_refund: self::SAMPLE_FEE_REFUND,
            denoms: array_map(
                fn(array $denom) => DenomCommon::fromArray($denom),
                $this->validData['denoms']
            ),
        );

        $denoms[] = DenomCommon::fromArray($this->validData['denoms'][0]);

        $this->assertSame(self::SAMPLE_VALUE, $group->getValue());
        $this->assertSame(self::SAMPLE_FEE_WITHDRAW, $group->getFeeWithdraw());
        $this->assertSame(self::SAMPLE_FEE_DEPOSIT, $group->getFeeDeposit());
        $this->assertSame(self::SAMPLE_FEE_REFRESH, $group->getFeeRefresh());
        $this->assertSame(self::SAMPLE_FEE_REFUND, $group->getFeeRefund());
        $this->assertSame('CS', $group->getCipher());
        $this->assertEquals($denoms, $group->getDenoms());
    }

    public function testFromArrayWithValidData(): void
    {
        $group = DenomGroupCs::fromArray($this->validData);

        $denoms[] = DenomCommon::fromArray($this->validData['denoms'][0]);

        $this->assertSame(self::SAMPLE_VALUE, $group->getValue());
        $this->assertSame(self::SAMPLE_FEE_WITHDRAW, $group->getFeeWithdraw());
        $this->assertSame(self::SAMPLE_FEE_DEPOSIT, $group->getFeeDeposit());
        $this->assertSame(self::SAMPLE_FEE_REFRESH, $group->getFeeRefresh());
        $this->assertSame(self::SAMPLE_FEE_REFUND, $group->getFeeRefund());
        $this->assertSame('CS', $group->getCipher());
        $this->assertEquals($denoms, $group->getDenoms());
    }

    public function testFromArrayWithInvalidCipher(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid cipher type "INVALID". Expected "CS"');

        $data = $this->validData;
        $data['cipher'] = 'INVALID';
        DenomGroupCs::fromArray($data);
    }

    public function testFromArrayWithLostDenom(): void
    {
        $data = $this->validData;
        $data['denoms'][0]['lost'] = true;

        $group = DenomGroupCs::fromArray($data);
        $denoms = $group->getDenoms();
        $this->assertTrue($denoms[0]->lost ?? false);
    }
} 
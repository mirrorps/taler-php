<?php

namespace Taler\Tests\Api\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\FutureDenom;
use Taler\Api\Dto\Timestamp;

/**
 * Test cases for FutureDenom DTO.
 */
class FutureDenomTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = [
            'section_name' => 'test_section',
            'value' => '10.00',
            'stamp_start' => '2024-01-01T00:00:00Z',
            'stamp_expire_withdraw' => '2024-12-31T23:59:59Z',
            'stamp_expire_deposit' => '2025-01-31T23:59:59Z',
            'stamp_expire_legal' => '2025-12-31T23:59:59Z',
            'denom_pub' => 'test_denom_pub_key',
            'fee_withdraw' => '0.50',
            'fee_deposit' => '0.25',
            'fee_refresh' => '0.10',
            'fee_refund' => '0.15',
            'denom_secmod_sig' => 'test_signature'
        ];

        $futureDenom = FutureDenom::createFromArray($data);

        $this->assertInstanceOf(FutureDenom::class, $futureDenom);
        $this->assertEquals($data['section_name'], $futureDenom->getSectionName());
        $this->assertEquals($data['value'], $futureDenom->getValue());
        $this->assertInstanceOf(Timestamp::class, $futureDenom->getStampStart());
        $this->assertInstanceOf(Timestamp::class, $futureDenom->getStampExpireWithdraw());
        $this->assertInstanceOf(Timestamp::class, $futureDenom->getStampExpireDeposit());
        $this->assertInstanceOf(Timestamp::class, $futureDenom->getStampExpireLegal());
        $this->assertEquals($data['denom_pub'], $futureDenom->getDenomPub());
        $this->assertEquals($data['fee_withdraw'], $futureDenom->getFeeWithdraw());
        $this->assertEquals($data['fee_deposit'], $futureDenom->getFeeDeposit());
        $this->assertEquals($data['fee_refresh'], $futureDenom->getFeeRefresh());
        $this->assertEquals($data['fee_refund'], $futureDenom->getFeeRefund());
        $this->assertEquals($data['denom_secmod_sig'], $futureDenom->getDenomSecmodSig());
    }
} 
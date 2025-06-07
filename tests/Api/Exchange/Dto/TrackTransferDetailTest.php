<?php

namespace Taler\Tests\Api\Exchange\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Exchange\Dto\TrackTransferDetail;

class TrackTransferDetailTest extends TestCase
{
    private const SAMPLE_H_CONTRACT_TERMS = 'AABBCCDDEEFF00112233445566778899AABBCCDDEEFF00112233445566778899AABBCCDDEEFF00112233445566778899AABBCCDDEEFF00112233445566778899';
    private const SAMPLE_COIN_PUB = '2Z5J4WXKMQK4A8RNXPTV1YBKM4506HXGWRZ7AAR4QX4QBVAJPX70';
    private const SAMPLE_DEPOSIT_VALUE = 'TALER:10';
    private const SAMPLE_DEPOSIT_FEE = 'TALER:0.5';
    private const SAMPLE_REFUND_TOTAL = 'TALER:2';

    public function testConstructWithRequiredParameters(): void
    {
        $detail = new TrackTransferDetail(
            h_contract_terms: self::SAMPLE_H_CONTRACT_TERMS,
            coin_pub: self::SAMPLE_COIN_PUB,
            deposit_value: self::SAMPLE_DEPOSIT_VALUE,
            deposit_fee: self::SAMPLE_DEPOSIT_FEE
        );

        $this->assertSame(self::SAMPLE_H_CONTRACT_TERMS, $detail->h_contract_terms);
        $this->assertSame(self::SAMPLE_COIN_PUB, $detail->coin_pub);
        $this->assertSame(self::SAMPLE_DEPOSIT_VALUE, $detail->deposit_value);
        $this->assertSame(self::SAMPLE_DEPOSIT_FEE, $detail->deposit_fee);
        $this->assertNull($detail->refund_total);
    }

    public function testConstructWithAllParameters(): void
    {
        $detail = new TrackTransferDetail(
            h_contract_terms: self::SAMPLE_H_CONTRACT_TERMS,
            coin_pub: self::SAMPLE_COIN_PUB,
            deposit_value: self::SAMPLE_DEPOSIT_VALUE,
            deposit_fee: self::SAMPLE_DEPOSIT_FEE,
            refund_total: self::SAMPLE_REFUND_TOTAL
        );

        $this->assertSame(self::SAMPLE_H_CONTRACT_TERMS, $detail->h_contract_terms);
        $this->assertSame(self::SAMPLE_COIN_PUB, $detail->coin_pub);
        $this->assertSame(self::SAMPLE_DEPOSIT_VALUE, $detail->deposit_value);
        $this->assertSame(self::SAMPLE_DEPOSIT_FEE, $detail->deposit_fee);
        $this->assertSame(self::SAMPLE_REFUND_TOTAL, $detail->refund_total);
    }

    public function testFromArrayWithRequiredParameters(): void
    {
        $data = [
            'h_contract_terms' => self::SAMPLE_H_CONTRACT_TERMS,
            'coin_pub' => self::SAMPLE_COIN_PUB,
            'deposit_value' => self::SAMPLE_DEPOSIT_VALUE,
            'deposit_fee' => self::SAMPLE_DEPOSIT_FEE,
        ];

        $detail = TrackTransferDetail::fromArray($data);

        $this->assertSame(self::SAMPLE_H_CONTRACT_TERMS, $detail->h_contract_terms);
        $this->assertSame(self::SAMPLE_COIN_PUB, $detail->coin_pub);
        $this->assertSame(self::SAMPLE_DEPOSIT_VALUE, $detail->deposit_value);
        $this->assertSame(self::SAMPLE_DEPOSIT_FEE, $detail->deposit_fee);
        $this->assertNull($detail->refund_total);
    }

    public function testFromArrayWithAllParameters(): void
    {
        $data = [
            'h_contract_terms' => self::SAMPLE_H_CONTRACT_TERMS,
            'coin_pub' => self::SAMPLE_COIN_PUB,
            'deposit_value' => self::SAMPLE_DEPOSIT_VALUE,
            'deposit_fee' => self::SAMPLE_DEPOSIT_FEE,
            'refund_total' => self::SAMPLE_REFUND_TOTAL,
        ];

        $detail = TrackTransferDetail::fromArray($data);

        $this->assertSame(self::SAMPLE_H_CONTRACT_TERMS, $detail->h_contract_terms);
        $this->assertSame(self::SAMPLE_COIN_PUB, $detail->coin_pub);
        $this->assertSame(self::SAMPLE_DEPOSIT_VALUE, $detail->deposit_value);
        $this->assertSame(self::SAMPLE_DEPOSIT_FEE, $detail->deposit_fee);
        $this->assertSame(self::SAMPLE_REFUND_TOTAL, $detail->refund_total);
    }

    public function testFromArrayWithNullRefundTotal(): void
    {
        $data = [
            'h_contract_terms' => self::SAMPLE_H_CONTRACT_TERMS,
            'coin_pub' => self::SAMPLE_COIN_PUB,
            'deposit_value' => self::SAMPLE_DEPOSIT_VALUE,
            'deposit_fee' => self::SAMPLE_DEPOSIT_FEE,
            'refund_total' => null,
        ];

        $detail = TrackTransferDetail::fromArray($data);

        $this->assertSame(self::SAMPLE_H_CONTRACT_TERMS, $detail->h_contract_terms);
        $this->assertSame(self::SAMPLE_COIN_PUB, $detail->coin_pub);
        $this->assertSame(self::SAMPLE_DEPOSIT_VALUE, $detail->deposit_value);
        $this->assertSame(self::SAMPLE_DEPOSIT_FEE, $detail->deposit_fee);
        $this->assertNull($detail->refund_total);
    }
} 
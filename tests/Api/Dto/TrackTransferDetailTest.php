<?php

namespace Taler\Tests\Api\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\TrackTransferDetail;

class TrackTransferDetailTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = [
            'h_contract_terms' => 'hash123',
            'coin_pub' => 'pubkey456',
            'deposit_value' => '100',
            'deposit_fee' => '10',
            'refund_total' => '5'
        ];

        $detail = TrackTransferDetail::createFromArray($data);

        $this->assertEquals($data['h_contract_terms'], $detail->getHContractTerms());
        $this->assertEquals($data['coin_pub'], $detail->getCoinPub());
        $this->assertEquals($data['deposit_value'], $detail->getDepositValue());
        $this->assertEquals($data['deposit_fee'], $detail->getDepositFee());
        $this->assertEquals($data['refund_total'], $detail->getRefundTotal());
    }

    public function testCreateFromArrayWithoutOptionalRefund(): void
    {
        $data = [
            'h_contract_terms' => 'hash123',
            'coin_pub' => 'pubkey456',
            'deposit_value' => '100',
            'deposit_fee' => '10'
        ];

        $detail = TrackTransferDetail::createFromArray($data);

        $this->assertEquals($data['h_contract_terms'], $detail->getHContractTerms());
        $this->assertEquals($data['coin_pub'], $detail->getCoinPub());
        $this->assertEquals($data['deposit_value'], $detail->getDepositValue());
        $this->assertEquals($data['deposit_fee'], $detail->getDepositFee());
        $this->assertNull($detail->getRefundTotal());
    }
} 
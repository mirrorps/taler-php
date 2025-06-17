<?php

namespace Taler\Tests\Api\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\TrackTransferResponse;

class TrackTransferResponseTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = [
            'total' => '1000',
            'wire_fee' => '10',
            'merchant_pub' => 'merchant_key123',
            'h_payto' => 'hash456',
            'execution_time' => ['t_s' => 1710936000], // 2024-03-20T12:00:00Z as Unix timestamp
            'deposits' => [
                [
                    'h_contract_terms' => 'hash123',
                    'coin_pub' => 'pubkey456',
                    'deposit_value' => '100',
                    'deposit_fee' => '10',
                    'refund_total' => '5'
                ],
                [
                    'h_contract_terms' => 'hash789',
                    'coin_pub' => 'pubkey012',
                    'deposit_value' => '200',
                    'deposit_fee' => '20'
                ]
            ],
            'exchange_sig' => 'signature789',
            'exchange_pub' => 'exchange_key456'
        ];

        $response = TrackTransferResponse::createFromArray($data);

        $this->assertEquals($data['total'], $response->getTotal());
        $this->assertEquals($data['wire_fee'], $response->getWireFee());
        $this->assertEquals($data['merchant_pub'], $response->getMerchantPub());
        $this->assertEquals($data['h_payto'], $response->getHPayto());
        $this->assertEquals($data['execution_time'], ['t_s' => $response->getExecutionTime()->t_s]);
        $this->assertEquals($data['exchange_sig'], $response->getExchangeSig());
        $this->assertEquals($data['exchange_pub'], $response->getExchangePub());

        $deposits = $response->getDeposits();
        $this->assertCount(2, $deposits);

        // Test first deposit
        $this->assertEquals($data['deposits'][0]['h_contract_terms'], $deposits[0]->getHContractTerms());
        $this->assertEquals($data['deposits'][0]['coin_pub'], $deposits[0]->getCoinPub());
        $this->assertEquals($data['deposits'][0]['deposit_value'], $deposits[0]->getDepositValue());
        $this->assertEquals($data['deposits'][0]['deposit_fee'], $deposits[0]->getDepositFee());
        $this->assertEquals($data['deposits'][0]['refund_total'], $deposits[0]->getRefundTotal());

        // Test second deposit
        $this->assertEquals($data['deposits'][1]['h_contract_terms'], $deposits[1]->getHContractTerms());
        $this->assertEquals($data['deposits'][1]['coin_pub'], $deposits[1]->getCoinPub());
        $this->assertEquals($data['deposits'][1]['deposit_value'], $deposits[1]->getDepositValue());
        $this->assertEquals($data['deposits'][1]['deposit_fee'], $deposits[1]->getDepositFee());
        $this->assertNull($deposits[1]->getRefundTotal());
    }

    public function testCreateFromArrayWithNeverTimestamp(): void
    {
        $data = [
            'total' => '1000',
            'wire_fee' => '10',
            'merchant_pub' => 'merchant_key123',
            'h_payto' => 'hash456',
            'execution_time' => ['t_s' => 'never'],
            'deposits' => [],
            'exchange_sig' => 'signature789',
            'exchange_pub' => 'exchange_key456'
        ];

        $response = TrackTransferResponse::createFromArray($data);
        $this->assertEquals($data['execution_time'], ['t_s' => $response->getExecutionTime()->t_s]);
    }
} 
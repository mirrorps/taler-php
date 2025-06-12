<?php

namespace Taler\Tests\Api\Exchange\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\Timestamp;
use Taler\Api\Exchange\Dto\TrackTransferResponse;
use Taler\Api\Exchange\Dto\TrackTransferDetail;

class TrackTransferResponseTest extends TestCase
{
    private const SAMPLE_TOTAL = 'TALER:100.50';
    private const SAMPLE_WIRE_FEE = 'TALER:0.50';
    private const SAMPLE_MERCHANT_PUB = '2Z5J4WXKMQK4A8RNXPTV1YBKM4506HXGWRZ7AAR4QX4QBVAJPX70';
    private const SAMPLE_H_PAYTO = 'AABBCCDDEEFF00112233445566778899AABBCCDDEEFF00112233445566778899';
    private const SAMPLE_EXECUTION_TIME = ['t_s' => 1710929400]; // 2024-03-20T10:30:00.000Z
    private const SAMPLE_EXCHANGE_SIG = 'EDDSAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';
    private const SAMPLE_EXCHANGE_PUB = '2Z5J4WXKMQK4A8RNXPTV1YBKM4506HXGWRZ7AAR4QX4QBVAJPX70';

    /**
     * @var array{
     *     h_contract_terms: string,
     *     coin_pub: string,
     *     deposit_value: string,
     *     deposit_fee: string,
     *     refund_total: string
     * }
     */
    private array $sampleDeposit;

    protected function setUp(): void
    {
        $this->sampleDeposit = [
            'h_contract_terms' => 'AABBCCDDEEFF00112233445566778899AABBCCDDEEFF00112233445566778899AABBCCDDEEFF00112233445566778899AABBCCDDEEFF00112233445566778899',
            'coin_pub' => '2Z5J4WXKMQK4A8RNXPTV1YBKM4506HXGWRZ7AAR4QX4QBVAJPX70',
            'deposit_value' => 'TALER:50.25',
            'deposit_fee' => 'TALER:0.25',
            'refund_total' => 'TALER:1.00'
        ];
    }

    public function testConstructWithSingleDeposit(): void
    {
        $depositDetail = TrackTransferDetail::fromArray($this->sampleDeposit);

        $response = new TrackTransferResponse(
            total: self::SAMPLE_TOTAL,
            wire_fee: self::SAMPLE_WIRE_FEE,
            merchant_pub: self::SAMPLE_MERCHANT_PUB,
            h_payto: self::SAMPLE_H_PAYTO,
            execution_time: new Timestamp(self::SAMPLE_EXECUTION_TIME['t_s']),
            deposits: [$depositDetail],
            exchange_sig: self::SAMPLE_EXCHANGE_SIG,
            exchange_pub: self::SAMPLE_EXCHANGE_PUB
        );

        $this->assertSame(self::SAMPLE_TOTAL, $response->total);
        $this->assertSame(self::SAMPLE_WIRE_FEE, $response->wire_fee);
        $this->assertSame(self::SAMPLE_MERCHANT_PUB, $response->merchant_pub);
        $this->assertSame(self::SAMPLE_H_PAYTO, $response->h_payto);
        $this->assertSame(self::SAMPLE_EXECUTION_TIME['t_s'], $response->execution_time->t_s);
        $this->assertSame(self::SAMPLE_EXCHANGE_SIG, $response->exchange_sig);
        $this->assertSame(self::SAMPLE_EXCHANGE_PUB, $response->exchange_pub);
        
        $this->assertCount(1, $response->deposits);
        $this->assertInstanceOf(TrackTransferDetail::class, $response->deposits[0]);
        $this->assertSame($this->sampleDeposit['h_contract_terms'], $response->deposits[0]->h_contract_terms);
        $this->assertSame($this->sampleDeposit['coin_pub'], $response->deposits[0]->coin_pub);
        $this->assertSame($this->sampleDeposit['deposit_value'], $response->deposits[0]->deposit_value);
        $this->assertSame($this->sampleDeposit['deposit_fee'], $response->deposits[0]->deposit_fee);
        $this->assertSame($this->sampleDeposit['refund_total'], $response->deposits[0]->refund_total);
    }

    public function testFromArrayWithMultipleDeposits(): void
    {
        /** @var array{
         *     h_contract_terms: string,
         *     coin_pub: string,
         *     deposit_value: string,
         *     deposit_fee: string,
         *     refund_total: string|null
         * } $secondDeposit
         */
        $secondDeposit = $this->sampleDeposit;
        $secondDeposit['deposit_value'] = 'TALER:49.75';
        $secondDeposit['refund_total'] = null;

        /** @var array{
         *     total: string,
         *     wire_fee: string,
         *     merchant_pub: string,
         *     h_payto: string,
         *     execution_time: array{t_s: int|string},
         *     deposits: array<int, array{h_contract_terms: string, coin_pub: string, deposit_value: string, deposit_fee: string, refund_total?: string|null}>,
         *     exchange_sig: string,
         *     exchange_pub: string
         * } $data
         */
        $data = [
            'total' => self::SAMPLE_TOTAL,
            'wire_fee' => self::SAMPLE_WIRE_FEE,
            'merchant_pub' => self::SAMPLE_MERCHANT_PUB,
            'h_payto' => self::SAMPLE_H_PAYTO,
            'execution_time' => self::SAMPLE_EXECUTION_TIME,
            'deposits' => [$this->sampleDeposit, $secondDeposit],
            'exchange_sig' => self::SAMPLE_EXCHANGE_SIG,
            'exchange_pub' => self::SAMPLE_EXCHANGE_PUB
        ];

        $response = TrackTransferResponse::fromArray($data);

        $this->assertSame(self::SAMPLE_TOTAL, $response->total);
        $this->assertSame(self::SAMPLE_WIRE_FEE, $response->wire_fee);
        $this->assertSame(self::SAMPLE_MERCHANT_PUB, $response->merchant_pub);
        $this->assertSame(self::SAMPLE_H_PAYTO, $response->h_payto);
        $this->assertSame(self::SAMPLE_EXECUTION_TIME['t_s'], $response->execution_time->t_s);
        $this->assertSame(self::SAMPLE_EXCHANGE_SIG, $response->exchange_sig);
        $this->assertSame(self::SAMPLE_EXCHANGE_PUB, $response->exchange_pub);
        
        $this->assertCount(2, $response->deposits);
        
        // First deposit assertions
        $this->assertInstanceOf(TrackTransferDetail::class, $response->deposits[0]);
        $this->assertSame($this->sampleDeposit['deposit_value'], $response->deposits[0]->deposit_value);
        $this->assertSame($this->sampleDeposit['refund_total'], $response->deposits[0]->refund_total);
        
        // Second deposit assertions
        $this->assertInstanceOf(TrackTransferDetail::class, $response->deposits[1]);
        $this->assertSame($secondDeposit['deposit_value'], $response->deposits[1]->deposit_value);
        $this->assertNull($response->deposits[1]->refund_total);
    }

    public function testFromArrayWithEmptyDeposits(): void
    {
        /** @var array{
         *     total: string,
         *     wire_fee: string,
         *     merchant_pub: string,
         *     h_payto: string,
         *     execution_time: array{t_s: int|string},
         *     deposits: array<int, array{h_contract_terms: string, coin_pub: string, deposit_value: string, deposit_fee: string, refund_total?: string|null}>,
         *     exchange_sig: string,
         *     exchange_pub: string
         * } $data
         */
        $data = [
            'total' => self::SAMPLE_TOTAL,
            'wire_fee' => self::SAMPLE_WIRE_FEE,
            'merchant_pub' => self::SAMPLE_MERCHANT_PUB,
            'h_payto' => self::SAMPLE_H_PAYTO,
            'execution_time' => self::SAMPLE_EXECUTION_TIME,
            'deposits' => [],
            'exchange_sig' => self::SAMPLE_EXCHANGE_SIG,
            'exchange_pub' => self::SAMPLE_EXCHANGE_PUB
        ];

        $response = TrackTransferResponse::fromArray($data);

        $this->assertSame(self::SAMPLE_TOTAL, $response->total);
        $this->assertCount(0, $response->deposits);
    }
} 
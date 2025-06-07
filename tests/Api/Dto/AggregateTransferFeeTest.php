<?php

namespace Taler\Tests\Api\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\AggregateTransferFee;

class AggregateTransferFeeTest extends TestCase
{
    private const SAMPLE_WIRE_FEE = 'TALER:0.50';
    private const SAMPLE_CLOSING_FEE = 'TALER:0.25';
    private const SAMPLE_START_DATE = '2024-03-20T00:00:00Z';
    private const SAMPLE_END_DATE = '2024-03-21T00:00:00Z';
    private const SAMPLE_SIG = 'EDDSAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';

    /** @var array{
     *     wire_fee?: string,
     *     closing_fee?: string,
     *     start_date?: string,
     *     end_date?: string,
     *     sig?: string
     * }
     */
    private array $validData;

    protected function setUp(): void
    {
        $this->validData = [
            'wire_fee' => self::SAMPLE_WIRE_FEE,
            'closing_fee' => self::SAMPLE_CLOSING_FEE,
            'start_date' => self::SAMPLE_START_DATE,
            'end_date' => self::SAMPLE_END_DATE,
            'sig' => self::SAMPLE_SIG
        ];
    }

    public function testConstructWithValidData(): void
    {
        $fee = new AggregateTransferFee(
            wire_fee: self::SAMPLE_WIRE_FEE,
            closing_fee: self::SAMPLE_CLOSING_FEE,
            start_date: self::SAMPLE_START_DATE,
            end_date: self::SAMPLE_END_DATE,
            sig: self::SAMPLE_SIG
        );

        $this->assertSame(self::SAMPLE_WIRE_FEE, $fee->wire_fee);
        $this->assertSame(self::SAMPLE_CLOSING_FEE, $fee->closing_fee);
        $this->assertSame(self::SAMPLE_START_DATE, $fee->start_date);
        $this->assertSame(self::SAMPLE_END_DATE, $fee->end_date);
        $this->assertSame(self::SAMPLE_SIG, $fee->sig);
    }

    public function testFromArrayWithValidData(): void
    {
        $fee = AggregateTransferFee::fromArray($this->validData);

        $this->assertSame(self::SAMPLE_WIRE_FEE, $fee->wire_fee);
        $this->assertSame(self::SAMPLE_CLOSING_FEE, $fee->closing_fee);
        $this->assertSame(self::SAMPLE_START_DATE, $fee->start_date);
        $this->assertSame(self::SAMPLE_END_DATE, $fee->end_date);
        $this->assertSame(self::SAMPLE_SIG, $fee->sig);
    }

    public function testFromArrayWithMissingWireFee(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing wire_fee field');

        $data = $this->validData;
        unset($data['wire_fee']);
        AggregateTransferFee::fromArray($data);
    }

    public function testFromArrayWithMissingClosingFee(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing closing_fee field');

        $data = $this->validData;
        unset($data['closing_fee']);
        AggregateTransferFee::fromArray($data);
    }

    public function testFromArrayWithMissingStartDate(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing start_date field');

        $data = $this->validData;
        unset($data['start_date']);
        AggregateTransferFee::fromArray($data);
    }

    public function testFromArrayWithMissingEndDate(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing end_date field');

        $data = $this->validData;
        unset($data['end_date']);
        AggregateTransferFee::fromArray($data);
    }

    public function testFromArrayWithMissingSig(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing sig field');

        $data = $this->validData;
        unset($data['sig']);
        AggregateTransferFee::fromArray($data);
    }

} 
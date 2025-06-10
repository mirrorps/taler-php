<?php

namespace Taler\Tests\Api\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\AggregateTransferFee;
use Taler\Api\Dto\Timestamp;

class AggregateTransferFeeTest extends TestCase
{
    private const SAMPLE_WIRE_FEE = 'TALER:0.50';
    private const SAMPLE_CLOSING_FEE = 'TALER:0.25';
    private const SAMPLE_START_DATE_S = 1710979200; // 2024-03-20T00:00:00Z in seconds
    private const SAMPLE_END_DATE_S = 1711065600; // 2024-03-21T00:00:00Z in seconds
    private const SAMPLE_START_DATE_STRING = '2024-03-20T00:00:00Z';
    private const SAMPLE_END_DATE_STRING = '2024-03-21T00:00:00Z';
    private const SAMPLE_SIG = 'EDDSAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';

    /** @var array{
     *     wire_fee?: string,
     *     closing_fee?: string,
     *     start_date?: array{t_s: int}|string,
     *     end_date?: array{t_s: int}|string,
     *     sig?: string
     * }
     */
    private array $validDataWithTimestamp;

    /** @var array{
     *     wire_fee?: string,
     *     closing_fee?: string,
     *     start_date?: string,
     *     end_date?: string,
     *     sig?: string
     * }
     */
    private array $validDataWithStringDates;

    protected function setUp(): void
    {
        $this->validDataWithTimestamp = [
            'wire_fee' => self::SAMPLE_WIRE_FEE,
            'closing_fee' => self::SAMPLE_CLOSING_FEE,
            'start_date' => ['t_s' => self::SAMPLE_START_DATE_S],
            'end_date' => ['t_s' => self::SAMPLE_END_DATE_S],
            'sig' => self::SAMPLE_SIG
        ];

        $this->validDataWithStringDates = [
            'wire_fee' => self::SAMPLE_WIRE_FEE,
            'closing_fee' => self::SAMPLE_CLOSING_FEE,
            'start_date' => self::SAMPLE_START_DATE_STRING,
            'end_date' => self::SAMPLE_END_DATE_STRING,
            'sig' => self::SAMPLE_SIG
        ];
    }

    public function testConstructWithValidDataTimestamp(): void
    {
        $fee = new AggregateTransferFee(
            wire_fee: self::SAMPLE_WIRE_FEE,
            closing_fee: self::SAMPLE_CLOSING_FEE,
            start_date: new Timestamp(self::SAMPLE_START_DATE_S),
            end_date: new Timestamp(self::SAMPLE_END_DATE_S),
            sig: self::SAMPLE_SIG
        );

        $this->assertSame(self::SAMPLE_WIRE_FEE, $fee->wire_fee);
        $this->assertSame(self::SAMPLE_CLOSING_FEE, $fee->closing_fee);
        $this->assertInstanceOf(Timestamp::class, $fee->start_date);
        $this->assertSame(self::SAMPLE_START_DATE_S, $fee->start_date->t_s);
        $this->assertInstanceOf(Timestamp::class, $fee->end_date);
        $this->assertSame(self::SAMPLE_END_DATE_S, $fee->end_date->t_s);
        $this->assertSame(self::SAMPLE_SIG, $fee->sig);
    }

    public function testConstructWithValidDataStringDates(): void
    {
        $fee = new AggregateTransferFee(
            wire_fee: self::SAMPLE_WIRE_FEE,
            closing_fee: self::SAMPLE_CLOSING_FEE,
            start_date: self::SAMPLE_START_DATE_STRING,
            end_date: self::SAMPLE_END_DATE_STRING,
            sig: self::SAMPLE_SIG
        );

        $this->assertSame(self::SAMPLE_WIRE_FEE, $fee->wire_fee);
        $this->assertSame(self::SAMPLE_CLOSING_FEE, $fee->closing_fee);
        $this->assertSame(self::SAMPLE_START_DATE_STRING, $fee->start_date);
        $this->assertSame(self::SAMPLE_END_DATE_STRING, $fee->end_date);
        $this->assertSame(self::SAMPLE_SIG, $fee->sig);
    }

    public function testFromArrayWithValidDataTimestamp(): void
    {
        $fee = AggregateTransferFee::fromArray($this->validDataWithTimestamp);

        $this->assertSame(self::SAMPLE_WIRE_FEE, $fee->wire_fee);
        $this->assertSame(self::SAMPLE_CLOSING_FEE, $fee->closing_fee);
        $this->assertInstanceOf(Timestamp::class, $fee->start_date);
        $this->assertSame(self::SAMPLE_START_DATE_S, $fee->start_date->t_s);
        $this->assertInstanceOf(Timestamp::class, $fee->end_date);
        $this->assertSame(self::SAMPLE_END_DATE_S, $fee->end_date->t_s);
        $this->assertSame(self::SAMPLE_SIG, $fee->sig);
    }

    public function testFromArrayWithValidDataStringDates(): void
    {
        $fee = AggregateTransferFee::fromArray($this->validDataWithStringDates);

        $this->assertSame(self::SAMPLE_WIRE_FEE, $fee->wire_fee);
        $this->assertSame(self::SAMPLE_CLOSING_FEE, $fee->closing_fee);
        $this->assertSame(self::SAMPLE_START_DATE_STRING, $fee->start_date);
        $this->assertSame(self::SAMPLE_END_DATE_STRING, $fee->end_date);
        $this->assertSame(self::SAMPLE_SIG, $fee->sig);
    }

    public function testFromArrayWithInvalidArrayFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid timestamp format:');

        $data = $this->validDataWithStringDates;
        $data['start_date'] = ['timestamp' => 1710979200];
        $data['end_date'] = ['timestamp' => 1711065600];

        AggregateTransferFee::fromArray($data); // @phpstan-ignore-line - Intentionally passing invalid data to test exception handling
    }

    public function testFromArrayWithMissingWireFee(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing wire_fee field');

        $data = $this->validDataWithStringDates;
        unset($data['wire_fee']);
        AggregateTransferFee::fromArray($data);
    }

    public function testFromArrayWithMissingClosingFee(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing closing_fee field');

        $data = $this->validDataWithStringDates;
        unset($data['closing_fee']);
        AggregateTransferFee::fromArray($data);
    }

    public function testFromArrayWithMissingStartDate(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing start_date field');

        $data = $this->validDataWithStringDates;
        unset($data['start_date']);
        AggregateTransferFee::fromArray($data);
    }

    public function testFromArrayWithMissingEndDate(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing end_date field');

        $data = $this->validDataWithStringDates;
        unset($data['end_date']);
        AggregateTransferFee::fromArray($data);
    }

    public function testFromArrayWithMissingSig(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing sig field');

        $data = $this->validDataWithStringDates;
        unset($data['sig']);
        AggregateTransferFee::fromArray($data);
    }

} 
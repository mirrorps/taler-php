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
    private const SAMPLE_SIG = 'EDDSAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';

    /** @var array{
     *     wire_fee: string,
     *     closing_fee: string,
     *     start_date: array{t_s: int|string},
     *     end_date: array{t_s: int|string},
     *     sig: string
     * }
     */
    private array $validData;

    protected function setUp(): void
    {
        $this->validData = [
            'wire_fee' => self::SAMPLE_WIRE_FEE,
            'closing_fee' => self::SAMPLE_CLOSING_FEE,
            'start_date' => ['t_s' => self::SAMPLE_START_DATE_S],
            'end_date' => ['t_s' => self::SAMPLE_END_DATE_S],
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

    public function testConstructWithInvalidDataTimestamp(): void
    {
        $this->expectException(\TypeError::class);

        new AggregateTransferFee( //@phpstan-ignore-line - testing error case
            wire_fee: self::SAMPLE_WIRE_FEE,
            closing_fee: self::SAMPLE_CLOSING_FEE,
            start_date: (object)['t_s' => self::SAMPLE_START_DATE_S], // @phpstan-ignore-line - Intentionally passing invalid data to test error handling
            end_date: new Timestamp(self::SAMPLE_END_DATE_S),
            sig: self::SAMPLE_SIG
        );
    }

    public function testFromArrayWithValidData(): void
    {
        $fee = AggregateTransferFee::fromArray($this->validData);

        $this->assertSame(self::SAMPLE_WIRE_FEE, $fee->wire_fee);
        $this->assertSame(self::SAMPLE_CLOSING_FEE, $fee->closing_fee);
        $this->assertInstanceOf(Timestamp::class, $fee->start_date);
        $this->assertSame(self::SAMPLE_START_DATE_S, $fee->start_date->t_s);
        $this->assertInstanceOf(Timestamp::class, $fee->end_date);
        $this->assertSame(self::SAMPLE_END_DATE_S, $fee->end_date->t_s);
        $this->assertSame(self::SAMPLE_SIG, $fee->sig);
    }

    public function testFromArrayWithInvalidData(): void
    {
        $this->expectException(\TypeError::class);

        /** @var array{
         *     wire_fee: string,
         *     closing_fee: string,
         *     start_date: array{t_s: int|string},
         *     end_date: array{t_s: int|string},
         *     sig: string
         * } $data
         */
        $data = $this->validData;
        $data['start_date'] = (array)new \stdClass();

        AggregateTransferFee::fromArray($data); //@phpstan-ignore-line - testing error case
    }
} 
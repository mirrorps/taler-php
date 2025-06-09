<?php

namespace Tests\Api\Exchange\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\RelativeTime;
use Taler\Api\Exchange\Dto\ExchangePartnerListEntry;

class ExchangePartnerListEntryTest extends TestCase
{
    private const SAMPLE_PARTNER_BASE_URL = 'https://exchange.partner.com';
    private const SAMPLE_PARTNER_MASTER_PUB = 'ED25519-PUB-NMLKJIHGFEDCBA987654321';
    private const SAMPLE_WAD_FEE = 'TALER:0.50';
    private const SAMPLE_START_DATE = '2024-03-20T00:00:00Z';
    private const SAMPLE_END_DATE = '2024-03-21T00:00:00Z';
    private const SAMPLE_MASTER_SIG = 'ED25519-SIG-ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /** @var array{
     *     partner_base_url: string,
     *     partner_master_pub: string,
     *     wad_fee: string,
     *     wad_frequency: array{d_us: int},
     *     start_date: string,
     *     end_date: string,
     *     master_sig: string
     * }
     */
    private array $validData;

    protected function setUp(): void
    {
        $this->validData = [
            'partner_base_url' => self::SAMPLE_PARTNER_BASE_URL,
            'partner_master_pub' => self::SAMPLE_PARTNER_MASTER_PUB,
            'wad_fee' => self::SAMPLE_WAD_FEE,
            'wad_frequency' => ['d_us' => 3600000000], // 1 hour in microseconds
            'start_date' => self::SAMPLE_START_DATE,
            'end_date' => self::SAMPLE_END_DATE,
            'master_sig' => self::SAMPLE_MASTER_SIG
        ];
    }

    public function testConstructWithValidData(): void
    {
        $wadFrequency = new RelativeTime(3600000000); // 1 hour in microseconds
        $entry = new ExchangePartnerListEntry(
            partner_base_url: self::SAMPLE_PARTNER_BASE_URL,
            partner_master_pub: self::SAMPLE_PARTNER_MASTER_PUB,
            wad_fee: self::SAMPLE_WAD_FEE,
            wad_frequency: $wadFrequency,
            start_date: self::SAMPLE_START_DATE,
            end_date: self::SAMPLE_END_DATE,
            master_sig: self::SAMPLE_MASTER_SIG
        );

        $this->assertSame(self::SAMPLE_PARTNER_BASE_URL, $entry->partner_base_url);
        $this->assertSame(self::SAMPLE_PARTNER_MASTER_PUB, $entry->partner_master_pub);
        $this->assertSame(self::SAMPLE_WAD_FEE, $entry->wad_fee);
        $this->assertSame($wadFrequency, $entry->wad_frequency);
        $this->assertSame(self::SAMPLE_START_DATE, $entry->start_date);
        $this->assertSame(self::SAMPLE_END_DATE, $entry->end_date);
        $this->assertSame(self::SAMPLE_MASTER_SIG, $entry->master_sig);
    }

    public function testFromArrayWithValidData(): void
    {
        $entry = ExchangePartnerListEntry::fromArray($this->validData);

        $this->assertSame(self::SAMPLE_PARTNER_BASE_URL, $entry->partner_base_url);
        $this->assertSame(self::SAMPLE_PARTNER_MASTER_PUB, $entry->partner_master_pub);
        $this->assertSame(self::SAMPLE_WAD_FEE, $entry->wad_fee);
        $this->assertSame(3600000000, $entry->wad_frequency->d_us);
        $this->assertSame(self::SAMPLE_START_DATE, $entry->start_date);
        $this->assertSame(self::SAMPLE_END_DATE, $entry->end_date);
        $this->assertSame(self::SAMPLE_MASTER_SIG, $entry->master_sig);
    }

    public function testFromArrayWithForeverFrequency(): void
    {
        $data = $this->validData;
        $data['wad_frequency'] = ['d_us' => 'forever'];

        $entry = ExchangePartnerListEntry::fromArray($data);

        $this->assertSame('forever', $entry->wad_frequency->d_us);
    }

    public function testObjectImmutability(): void
    {
        $entry = ExchangePartnerListEntry::fromArray($this->validData);

        // Verify that all properties are readonly
        $this->assertTrue((new \ReflectionProperty($entry, 'partner_base_url'))->isReadOnly());
        $this->assertTrue((new \ReflectionProperty($entry, 'partner_master_pub'))->isReadOnly());
        $this->assertTrue((new \ReflectionProperty($entry, 'wad_fee'))->isReadOnly());
        $this->assertTrue((new \ReflectionProperty($entry, 'wad_frequency'))->isReadOnly());
        $this->assertTrue((new \ReflectionProperty($entry, 'start_date'))->isReadOnly());
        $this->assertTrue((new \ReflectionProperty($entry, 'end_date'))->isReadOnly());
        $this->assertTrue((new \ReflectionProperty($entry, 'master_sig'))->isReadOnly());
    }
} 
<?php

namespace Taler\Tests\Api\ContractTerms\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\ContractTerms\Dto\TokenIssueCsPublicKey;
use Taler\Api\Dto\Timestamp;

class TokenIssueCsPublicKeyTest extends TestCase
{
    private const SAMPLE_CS_PUB = 'CS25519-PUB-123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private const SAMPLE_VALIDITY_START_S = 1710979200; // 2024-03-20T00:00:00Z in seconds
    private const SAMPLE_VALIDITY_END_S = 1711065600; // 2024-03-21T00:00:00Z in seconds

    public function testConstructWithValidData(): void
    {
        $validityStart = new Timestamp(self::SAMPLE_VALIDITY_START_S);
        $validityEnd = new Timestamp(self::SAMPLE_VALIDITY_END_S);

        $key = new TokenIssueCsPublicKey(
            cs_pub: self::SAMPLE_CS_PUB,
            signature_validity_start: $validityStart,
            signature_validity_end: $validityEnd
        );

        $this->assertSame(self::SAMPLE_CS_PUB, $key->cs_pub);
        $this->assertSame($validityStart, $key->signature_validity_start);
        $this->assertSame($validityEnd, $key->signature_validity_end);
        $this->assertSame('CS', $key->getCipher());
    }

    public function testCreateFromArrayWithValidData(): void
    {
        $data = [
            'cs_pub' => self::SAMPLE_CS_PUB,
            'signature_validity_start' => ['t_s' => self::SAMPLE_VALIDITY_START_S],
            'signature_validity_end' => ['t_s' => self::SAMPLE_VALIDITY_END_S]
        ];

        $key = TokenIssueCsPublicKey::createFromArray($data);

        $this->assertSame(self::SAMPLE_CS_PUB, $key->cs_pub);
        $this->assertInstanceOf(Timestamp::class, $key->signature_validity_start);
        $this->assertSame(self::SAMPLE_VALIDITY_START_S, $key->signature_validity_start->t_s);
        $this->assertInstanceOf(Timestamp::class, $key->signature_validity_end);
        $this->assertSame(self::SAMPLE_VALIDITY_END_S, $key->signature_validity_end->t_s);
        $this->assertSame('CS', $key->getCipher());
    }

    public function testCreateFromArrayWithNeverTimestamp(): void
    {
        $data = [
            'cs_pub' => self::SAMPLE_CS_PUB,
            'signature_validity_start' => ['t_s' => self::SAMPLE_VALIDITY_START_S],
            'signature_validity_end' => ['t_s' => 'never']
        ];

        $key = TokenIssueCsPublicKey::createFromArray($data);

        $this->assertSame(self::SAMPLE_CS_PUB, $key->cs_pub);
        $this->assertInstanceOf(Timestamp::class, $key->signature_validity_start);
        $this->assertSame(self::SAMPLE_VALIDITY_START_S, $key->signature_validity_start->t_s);
        $this->assertInstanceOf(Timestamp::class, $key->signature_validity_end);
        $this->assertSame('never', $key->signature_validity_end->t_s);
        $this->assertSame('CS', $key->getCipher());
    }
} 
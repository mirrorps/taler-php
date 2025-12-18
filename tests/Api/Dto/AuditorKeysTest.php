<?php

namespace Taler\Tests\Api\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\AuditorKeys;
use Taler\Api\Dto\AuditorDenominationKey;

class AuditorKeysTest extends TestCase
{
    private const SAMPLE_AUDITOR_PUB = 'sample_auditor_pub';
    private const SAMPLE_AUDITOR_URL = 'https://example.com/auditor';
    private const SAMPLE_AUDITOR_NAME = 'Sample Auditor';
    private const SAMPLE_DENOM_PUB_H = 'sample_denom_pub_h';
    private const SAMPLE_AUDITOR_SIG = 'sample_auditor_sig';

    public function testConstructorWithValidData(): void
    {
        $denominationKey = new AuditorDenominationKey(
            self::SAMPLE_DENOM_PUB_H,
            self::SAMPLE_AUDITOR_SIG
        );

        $dto = new AuditorKeys(
            self::SAMPLE_AUDITOR_PUB,
            self::SAMPLE_AUDITOR_URL,
            self::SAMPLE_AUDITOR_NAME,
            [$denominationKey]
        );

        $this->assertEquals(self::SAMPLE_AUDITOR_PUB, $dto->auditor_pub);
        $this->assertEquals(self::SAMPLE_AUDITOR_URL, $dto->auditor_url);
        $this->assertEquals(self::SAMPLE_AUDITOR_NAME, $dto->auditor_name);
        $this->assertCount(1, $dto->denomination_keys);
        $this->assertEquals(self::SAMPLE_DENOM_PUB_H, $dto->denomination_keys[0]->denom_pub_h);
        $this->assertEquals(self::SAMPLE_AUDITOR_SIG, $dto->denomination_keys[0]->auditor_sig);
    }

    public function testFromArrayWithValidData(): void
    {
        $data = [
            'auditor_pub' => self::SAMPLE_AUDITOR_PUB,
            'auditor_url' => self::SAMPLE_AUDITOR_URL,
            'auditor_name' => self::SAMPLE_AUDITOR_NAME,
            'denomination_keys' => [
                [
                    'denom_pub_h' => self::SAMPLE_DENOM_PUB_H,
                    'auditor_sig' => self::SAMPLE_AUDITOR_SIG,
                ],
            ],
        ];

        $dto = AuditorKeys::createFromArray($data);

        $this->assertEquals(self::SAMPLE_AUDITOR_PUB, $dto->auditor_pub);
        $this->assertEquals(self::SAMPLE_AUDITOR_URL, $dto->auditor_url);
        $this->assertEquals(self::SAMPLE_AUDITOR_NAME, $dto->auditor_name);
        $this->assertCount(1, $dto->denomination_keys);
        $this->assertEquals(self::SAMPLE_DENOM_PUB_H, $dto->denomination_keys[0]->denom_pub_h);
        $this->assertEquals(self::SAMPLE_AUDITOR_SIG, $dto->denomination_keys[0]->auditor_sig);
    }
} 
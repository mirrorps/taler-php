<?php

namespace Taler\Tests\Api\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\AuditorDenominationKey;

class AuditorDenominationKeyTest extends TestCase
{
    private const SAMPLE_DENOM_PUB_H = 'sample_denom_pub_h';
    private const SAMPLE_AUDITOR_SIG = 'sample_auditor_sig';

    public function testConstructorWithValidData(): void
    {
        $dto = new AuditorDenominationKey(self::SAMPLE_DENOM_PUB_H, self::SAMPLE_AUDITOR_SIG);

        $this->assertEquals(self::SAMPLE_DENOM_PUB_H, $dto->denom_pub_h);
        $this->assertEquals(self::SAMPLE_AUDITOR_SIG, $dto->auditor_sig);
    }

    public function testFromArrayWithValidData(): void
    {
        $data = [
            'denom_pub_h' => self::SAMPLE_DENOM_PUB_H,
            'auditor_sig' => self::SAMPLE_AUDITOR_SIG,
        ];

        $dto = AuditorDenominationKey::createFromArray($data);

        $this->assertEquals(self::SAMPLE_DENOM_PUB_H, $dto->denom_pub_h);
        $this->assertEquals(self::SAMPLE_AUDITOR_SIG, $dto->auditor_sig);
    }
} 
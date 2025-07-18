<?php

namespace Taler\Tests\Api\ContractTerms\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\ContractTerms\Dto\ContractTermsV0;

class ContractTermsV0Test extends TestCase
{
    private const SAMPLE_AMOUNT = 'TALER:10';
    private const SAMPLE_MAX_FEE = 'TALER:0.5';

    public function testConstruct(): void
    {
        $contractTerms = new ContractTermsV0(
            amount: self::SAMPLE_AMOUNT,
            max_fee: self::SAMPLE_MAX_FEE
        );

        $this->assertSame(self::SAMPLE_AMOUNT, $contractTerms->amount);
        $this->assertSame(self::SAMPLE_MAX_FEE, $contractTerms->max_fee);
        $this->assertSame(0, $contractTerms->version);
    }

    public function testConstructWithCustomVersion(): void
    {
        $contractTerms = new ContractTermsV0(
            amount: self::SAMPLE_AMOUNT,
            max_fee: self::SAMPLE_MAX_FEE,
            version: 0
        );

        $this->assertSame(self::SAMPLE_AMOUNT, $contractTerms->amount);
        $this->assertSame(self::SAMPLE_MAX_FEE, $contractTerms->max_fee);
        $this->assertSame(0, $contractTerms->version);
    }

    public function testFromArrayWithRequiredParameters(): void
    {
        $data = [
            'amount' => self::SAMPLE_AMOUNT,
            'max_fee' => self::SAMPLE_MAX_FEE
        ];

        $contractTerms = ContractTermsV0::createFromArray($data);

        $this->assertSame(self::SAMPLE_AMOUNT, $contractTerms->amount);
        $this->assertSame(self::SAMPLE_MAX_FEE, $contractTerms->max_fee);
        $this->assertSame(0, $contractTerms->version);
    }

    public function testFromArrayWithAllParameters(): void
    {
        $data = [
            'amount' => self::SAMPLE_AMOUNT,
            'max_fee' => self::SAMPLE_MAX_FEE,
            'version' => 0
        ];

        $contractTerms = ContractTermsV0::createFromArray($data);

        $this->assertSame(self::SAMPLE_AMOUNT, $contractTerms->amount);
        $this->assertSame(self::SAMPLE_MAX_FEE, $contractTerms->max_fee);
        $this->assertSame(0, $contractTerms->version);
    }
} 
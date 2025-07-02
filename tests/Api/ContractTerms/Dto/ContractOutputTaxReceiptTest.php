<?php

namespace Taler\Tests\Api\ContractTerms\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\ContractTerms\Dto\ContractOutputTaxReceipt;

class ContractOutputTaxReceiptTest extends TestCase
{
    private const SAMPLE_DONAU_URLS = [
        'https://donau1.example.com',
        'https://donau2.example.com'
    ];
    private const SAMPLE_AMOUNT = 'TALER:10';

    public function testConstruct(): void
    {
        $contractOutput = new ContractOutputTaxReceipt(
            donau_urls: self::SAMPLE_DONAU_URLS,
            amount: self::SAMPLE_AMOUNT
        );

        $this->assertSame(self::SAMPLE_DONAU_URLS, $contractOutput->donau_urls);
        $this->assertSame(self::SAMPLE_AMOUNT, $contractOutput->amount);
        $this->assertSame('tax-receipt', $contractOutput->getType());
    }

    public function testCreateFromArray(): void
    {
        $data = [
            'donau_urls' => self::SAMPLE_DONAU_URLS,
            'amount' => self::SAMPLE_AMOUNT
        ];

        $contractOutput = ContractOutputTaxReceipt::createFromArray($data);

        $this->assertSame(self::SAMPLE_DONAU_URLS, $contractOutput->donau_urls);
        $this->assertSame(self::SAMPLE_AMOUNT, $contractOutput->amount);
        $this->assertSame('tax-receipt', $contractOutput->getType());
    }
} 
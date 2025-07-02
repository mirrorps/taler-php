<?php

namespace Taler\Tests\Api\ContractTerms\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\ContractTerms\Dto\ContractDiscountTokenDetails;

class ContractDiscountTokenDetailsTest extends TestCase
{
    private const SAMPLE_EXPECTED_DOMAINS = ['shop.example.com', '*.shop.example.org', 'discount.example.net'];

    public function testConstruct(): void
    {
        $tokenDetails = new ContractDiscountTokenDetails(
            expected_domains: self::SAMPLE_EXPECTED_DOMAINS
        );

        $this->assertSame(self::SAMPLE_EXPECTED_DOMAINS, $tokenDetails->expected_domains);
        $this->assertSame('discount', $tokenDetails->getClass());
    }

    public function testCreateFromArray(): void
    {
        $data = [
            'expected_domains' => self::SAMPLE_EXPECTED_DOMAINS
        ];

        $tokenDetails = ContractDiscountTokenDetails::createFromArray($data);

        $this->assertSame(self::SAMPLE_EXPECTED_DOMAINS, $tokenDetails->expected_domains);
        $this->assertSame('discount', $tokenDetails->getClass());
    }
} 
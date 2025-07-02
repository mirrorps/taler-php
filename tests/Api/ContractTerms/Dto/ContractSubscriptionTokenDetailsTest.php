<?php

namespace Taler\Tests\Api\ContractTerms\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\ContractTerms\Dto\ContractSubscriptionTokenDetails;

class ContractSubscriptionTokenDetailsTest extends TestCase
{
    private const SAMPLE_TRUSTED_DOMAINS = ['example.com', '*.example.org', 'test.example.net'];

    public function testConstruct(): void
    {
        $tokenDetails = new ContractSubscriptionTokenDetails(
            trusted_domains: self::SAMPLE_TRUSTED_DOMAINS
        );

        $this->assertSame(self::SAMPLE_TRUSTED_DOMAINS, $tokenDetails->trusted_domains);
        $this->assertSame('subscription', $tokenDetails->getClass());
    }

    public function testCreateFromArray(): void
    {
        $data = [
            'trusted_domains' => self::SAMPLE_TRUSTED_DOMAINS
        ];

        $tokenDetails = ContractSubscriptionTokenDetails::createFromArray($data);

        $this->assertSame(self::SAMPLE_TRUSTED_DOMAINS, $tokenDetails->trusted_domains);
        $this->assertSame('subscription', $tokenDetails->getClass());
    }
} 
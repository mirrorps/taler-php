<?php

namespace Taler\Tests\Api\ContractTerms\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\ContractTerms\Dto\ContractTokenFamily;
use Taler\Api\ContractTerms\Dto\TokenIssueRsaPublicKey;
use Taler\Api\ContractTerms\Dto\TokenIssueCsPublicKey;
use Taler\Api\ContractTerms\Dto\ContractSubscriptionTokenDetails;
use Taler\Api\ContractTerms\Dto\ContractDiscountTokenDetails;
use Taler\Api\Dto\Timestamp;

class ContractTokenFamilyTest extends TestCase
{
    private const SAMPLE_NAME = 'Test Token Family';
    private const SAMPLE_DESCRIPTION = 'A test token family for testing purposes';
    private const SAMPLE_DESCRIPTION_I18N = [
        'de' => 'Eine Test-Token-Familie für Testzwecke',
        'fr' => 'Une famille de jetons de test à des fins de test'
    ];
    private const SAMPLE_RSA_PUB = 'RSA-PUB-123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private const SAMPLE_CS_PUB = 'CS25519-PUB-123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private const SAMPLE_VALIDITY_START_S = 1710979200; // 2024-03-20T00:00:00Z in seconds
    private const SAMPLE_VALIDITY_END_S = 1711065600; // 2024-03-21T00:00:00Z in seconds
    private const SAMPLE_TRUSTED_DOMAINS = ['example.com', '*.example.org'];
    private const SAMPLE_EXPECTED_DOMAINS = ['shop.example.com', '*.shop.example.org'];

    public function testConstructWithSubscriptionDetails(): void
    {
        $validityStart = new Timestamp(self::SAMPLE_VALIDITY_START_S);
        $validityEnd = new Timestamp(self::SAMPLE_VALIDITY_END_S);

        $rsaKey = new TokenIssueRsaPublicKey(
            rsa_pub: self::SAMPLE_RSA_PUB,
            signature_validity_start: $validityStart,
            signature_validity_end: $validityEnd
        );

        $csKey = new TokenIssueCsPublicKey(
            cs_pub: self::SAMPLE_CS_PUB,
            signature_validity_start: $validityStart,
            signature_validity_end: $validityEnd
        );

        $details = new ContractSubscriptionTokenDetails(
            trusted_domains: self::SAMPLE_TRUSTED_DOMAINS
        );

        $tokenFamily = new ContractTokenFamily(
            name: self::SAMPLE_NAME,
            description: self::SAMPLE_DESCRIPTION,
            description_i18n: self::SAMPLE_DESCRIPTION_I18N,
            keys: [$rsaKey, $csKey],
            details: $details,
            critical: true
        );

        $this->assertSame(self::SAMPLE_NAME, $tokenFamily->name);
        $this->assertSame(self::SAMPLE_DESCRIPTION, $tokenFamily->description);
        $this->assertSame(self::SAMPLE_DESCRIPTION_I18N, $tokenFamily->description_i18n);
        $this->assertCount(2, $tokenFamily->keys);
        $this->assertInstanceOf(TokenIssueRsaPublicKey::class, $tokenFamily->keys[0]);
        $this->assertInstanceOf(TokenIssueCsPublicKey::class, $tokenFamily->keys[1]);
        $this->assertInstanceOf(ContractSubscriptionTokenDetails::class, $tokenFamily->details);
        $this->assertTrue($tokenFamily->critical);
    }

    public function testConstructWithDiscountDetails(): void
    {
        $validityStart = new Timestamp(self::SAMPLE_VALIDITY_START_S);
        $validityEnd = new Timestamp(self::SAMPLE_VALIDITY_END_S);

        $rsaKey = new TokenIssueRsaPublicKey(
            rsa_pub: self::SAMPLE_RSA_PUB,
            signature_validity_start: $validityStart,
            signature_validity_end: $validityEnd
        );

        $details = new ContractDiscountTokenDetails(
            expected_domains: self::SAMPLE_EXPECTED_DOMAINS
        );

        $tokenFamily = new ContractTokenFamily(
            name: self::SAMPLE_NAME,
            description: self::SAMPLE_DESCRIPTION,
            description_i18n: null,
            keys: [$rsaKey],
            details: $details,
            critical: false
        );

        $this->assertSame(self::SAMPLE_NAME, $tokenFamily->name);
        $this->assertSame(self::SAMPLE_DESCRIPTION, $tokenFamily->description);
        $this->assertNull($tokenFamily->description_i18n);
        $this->assertCount(1, $tokenFamily->keys);
        $this->assertInstanceOf(TokenIssueRsaPublicKey::class, $tokenFamily->keys[0]);
        $this->assertInstanceOf(ContractDiscountTokenDetails::class, $tokenFamily->details);
        $this->assertFalse($tokenFamily->critical);
    }

    public function testCreateFromArrayWithSubscriptionDetails(): void
    {
        $data = [
            'name' => self::SAMPLE_NAME,
            'description' => self::SAMPLE_DESCRIPTION,
            'description_i18n' => self::SAMPLE_DESCRIPTION_I18N,
            'keys' => [
                [
                    'cipher' => 'RSA',
                    'rsa_pub' => self::SAMPLE_RSA_PUB,
                    'signature_validity_start' => ['t_s' => self::SAMPLE_VALIDITY_START_S],
                    'signature_validity_end' => ['t_s' => self::SAMPLE_VALIDITY_END_S]
                ],
                [
                    'cipher' => 'CS',
                    'cs_pub' => self::SAMPLE_CS_PUB,
                    'signature_validity_start' => ['t_s' => self::SAMPLE_VALIDITY_START_S],
                    'signature_validity_end' => ['t_s' => self::SAMPLE_VALIDITY_END_S]
                ]
            ],
            'details' => [
                'class' => 'subscription',
                'trusted_domains' => self::SAMPLE_TRUSTED_DOMAINS
            ],
            'critical' => true
        ];

        $tokenFamily = ContractTokenFamily::createFromArray($data);

        $this->assertSame(self::SAMPLE_NAME, $tokenFamily->name);
        $this->assertSame(self::SAMPLE_DESCRIPTION, $tokenFamily->description);
        $this->assertSame(self::SAMPLE_DESCRIPTION_I18N, $tokenFamily->description_i18n);
        $this->assertCount(2, $tokenFamily->keys);
        $this->assertInstanceOf(TokenIssueRsaPublicKey::class, $tokenFamily->keys[0]);
        $this->assertInstanceOf(TokenIssueCsPublicKey::class, $tokenFamily->keys[1]);
        $this->assertInstanceOf(ContractSubscriptionTokenDetails::class, $tokenFamily->details);
        $this->assertTrue($tokenFamily->critical);
    }

    public function testCreateFromArrayWithDiscountDetails(): void
    {
        $data = [
            'name' => self::SAMPLE_NAME,
            'description' => self::SAMPLE_DESCRIPTION,
            'keys' => [
                [
                    'cipher' => 'RSA',
                    'rsa_pub' => self::SAMPLE_RSA_PUB,
                    'signature_validity_start' => ['t_s' => self::SAMPLE_VALIDITY_START_S],
                    'signature_validity_end' => ['t_s' => self::SAMPLE_VALIDITY_END_S]
                ]
            ],
            'details' => [
                'class' => 'discount',
                'expected_domains' => self::SAMPLE_EXPECTED_DOMAINS
            ],
            'critical' => false
        ];

        $tokenFamily = ContractTokenFamily::createFromArray($data);

        $this->assertSame(self::SAMPLE_NAME, $tokenFamily->name);
        $this->assertSame(self::SAMPLE_DESCRIPTION, $tokenFamily->description);
        $this->assertNull($tokenFamily->description_i18n);
        $this->assertCount(1, $tokenFamily->keys);
        $this->assertInstanceOf(TokenIssueRsaPublicKey::class, $tokenFamily->keys[0]);
        $this->assertInstanceOf(ContractDiscountTokenDetails::class, $tokenFamily->details);
        $this->assertFalse($tokenFamily->critical);
    }

    public function testCreateFromArrayWithInvalidKeyData(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid or incomplete key data for cipher type "RSA"');

        $data = [
            'name' => self::SAMPLE_NAME,
            'description' => self::SAMPLE_DESCRIPTION,
            'keys' => [
                [
                    'cipher' => 'RSA',
                    'signature_validity_start' => ['t_s' => self::SAMPLE_VALIDITY_START_S],
                    'signature_validity_end' => ['t_s' => self::SAMPLE_VALIDITY_END_S]
                ]
            ],
            'details' => [
                'class' => 'subscription',
                'trusted_domains' => self::SAMPLE_TRUSTED_DOMAINS
            ],
            'critical' => true
        ];

        ContractTokenFamily::createFromArray($data);
    }

    public function testCreateFromArrayWithInvalidDetailsData(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid or incomplete token details for class "subscription"');

        $data = [
            'name' => self::SAMPLE_NAME,
            'description' => self::SAMPLE_DESCRIPTION,
            'keys' => [
                [
                    'cipher' => 'RSA',
                    'rsa_pub' => self::SAMPLE_RSA_PUB,
                    'signature_validity_start' => ['t_s' => self::SAMPLE_VALIDITY_START_S],
                    'signature_validity_end' => ['t_s' => self::SAMPLE_VALIDITY_END_S]
                ]
            ],
            'details' => [
                'class' => 'subscription'
            ],
            'critical' => true
        ];

        ContractTokenFamily::createFromArray($data);
    }
} 
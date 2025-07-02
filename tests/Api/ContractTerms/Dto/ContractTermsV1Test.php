<?php

namespace Taler\Tests\Api\ContractTerms\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\ContractTerms\Dto\ContractTermsV1;
use Taler\Api\ContractTerms\Dto\ContractChoice;
use Taler\Api\ContractTerms\Dto\ContractTokenFamily;
use Taler\Api\ContractTerms\Dto\ContractInputToken;
use Taler\Api\ContractTerms\Dto\ContractOutputToken;
use Taler\Api\ContractTerms\Dto\ContractOutputTaxReceipt;
use Taler\Api\ContractTerms\Dto\TokenIssueRsaPublicKey;
use Taler\Api\ContractTerms\Dto\ContractSubscriptionTokenDetails;
use Taler\Api\Dto\Timestamp;

class ContractTermsV1Test extends TestCase
{
    private const SAMPLE_AMOUNT = 'EUR:10';
    private const SAMPLE_MAX_FEE = 'EUR:0.5';
    private const SAMPLE_TOKEN_FAMILY_SLUG = 'test-token-family';
    private const SAMPLE_KEY_INDEX = 1;
    private const SAMPLE_COUNT = 2;
    private const SAMPLE_DONAU_URLS = ['https://donau1.example.com', 'https://donau2.example.com'];
    private const SAMPLE_TAX_AMOUNT = 'EUR:2';
    private const SAMPLE_NAME = 'Test Token Family';
    private const SAMPLE_DESCRIPTION = 'A test token family for testing purposes';
    private const SAMPLE_DESCRIPTION_I18N = [
        'de' => 'Eine Test-Token-Familie für Testzwecke',
        'fr' => 'Une famille de jetons de test à des fins de test'
    ];
    private const SAMPLE_RSA_PUB = 'RSA-PUB-123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private const SAMPLE_VALIDITY_START_S = 1710979200; // 2024-03-20T00:00:00Z in seconds
    private const SAMPLE_VALIDITY_END_S = 1711065600; // 2024-03-21T00:00:00Z in seconds
    private const SAMPLE_TRUSTED_DOMAINS = ['example.com', '*.example.org'];

    public function testConstruct(): void
    {
        $validityStart = new Timestamp(self::SAMPLE_VALIDITY_START_S);
        $validityEnd = new Timestamp(self::SAMPLE_VALIDITY_END_S);

        $inputs = [
            new ContractInputToken(
                token_family_slug: self::SAMPLE_TOKEN_FAMILY_SLUG,
                count: self::SAMPLE_COUNT
            )
        ];

        $outputs = [
            new ContractOutputToken(
                token_family_slug: self::SAMPLE_TOKEN_FAMILY_SLUG,
                key_index: self::SAMPLE_KEY_INDEX,
                count: self::SAMPLE_COUNT
            ),
            new ContractOutputTaxReceipt(
                donau_urls: self::SAMPLE_DONAU_URLS,
                amount: self::SAMPLE_TAX_AMOUNT
            )
        ];

        $choice = new ContractChoice(
            amount: self::SAMPLE_AMOUNT,
            inputs: $inputs,
            outputs: $outputs,
            max_fee: self::SAMPLE_MAX_FEE
        );

        $rsaKey = new TokenIssueRsaPublicKey(
            rsa_pub: self::SAMPLE_RSA_PUB,
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
            keys: [$rsaKey],
            details: $details,
            critical: true
        );

        $contractTerms = new ContractTermsV1(
            choices: [$choice],
            token_families: [self::SAMPLE_TOKEN_FAMILY_SLUG => $tokenFamily]
        );

        $this->assertCount(1, $contractTerms->choices);
        $this->assertInstanceOf(ContractChoice::class, $contractTerms->choices[0]);
        $this->assertCount(1, $contractTerms->token_families);
        $this->assertInstanceOf(ContractTokenFamily::class, $contractTerms->token_families[self::SAMPLE_TOKEN_FAMILY_SLUG]);
        $this->assertSame(1, $contractTerms->getVersion());
    }

    public function testCreateFromArray(): void
    {
        $data = [
            'choices' => [
                [
                    'amount' => self::SAMPLE_AMOUNT,
                    'inputs' => [
                        [
                            'token_family_slug' => self::SAMPLE_TOKEN_FAMILY_SLUG,
                            'count' => self::SAMPLE_COUNT
                        ]
                    ],
                    'outputs' => [
                        [
                            'token_family_slug' => self::SAMPLE_TOKEN_FAMILY_SLUG,
                            'key_index' => self::SAMPLE_KEY_INDEX,
                            'count' => self::SAMPLE_COUNT
                        ],
                        [
                            'donau_urls' => self::SAMPLE_DONAU_URLS,
                            'amount' => self::SAMPLE_TAX_AMOUNT
                        ]
                    ],
                    'max_fee' => self::SAMPLE_MAX_FEE
                ]
            ],
            'token_families' => [
                self::SAMPLE_TOKEN_FAMILY_SLUG => [
                    'name' => self::SAMPLE_NAME,
                    'description' => self::SAMPLE_DESCRIPTION,
                    'description_i18n' => self::SAMPLE_DESCRIPTION_I18N,
                    'keys' => [
                        [
                            'cipher' => 'RSA',
                            'rsa_pub' => self::SAMPLE_RSA_PUB,
                            'signature_validity_start' => ['t_s' => self::SAMPLE_VALIDITY_START_S],
                            'signature_validity_end' => ['t_s' => self::SAMPLE_VALIDITY_END_S]
                        ]
                    ],
                    'details' => [
                        'class' => 'subscription',
                        'trusted_domains' => self::SAMPLE_TRUSTED_DOMAINS
                    ],
                    'critical' => true
                ]
            ]
        ];

        $contractTerms = ContractTermsV1::createFromArray($data);

        $this->assertCount(1, $contractTerms->choices);
        $this->assertInstanceOf(ContractChoice::class, $contractTerms->choices[0]);
        $this->assertCount(1, $contractTerms->token_families);
        $this->assertInstanceOf(ContractTokenFamily::class, $contractTerms->token_families[self::SAMPLE_TOKEN_FAMILY_SLUG]);
        $this->assertSame(1, $contractTerms->getVersion());

        // Test choice details
        $choice = $contractTerms->choices[0];
        $this->assertSame(self::SAMPLE_AMOUNT, $choice->amount);
        $this->assertSame(self::SAMPLE_MAX_FEE, $choice->max_fee);
        $this->assertCount(1, $choice->inputs);
        $this->assertCount(2, $choice->outputs);

        // Test token family details
        $tokenFamily = $contractTerms->token_families[self::SAMPLE_TOKEN_FAMILY_SLUG];
        $this->assertSame(self::SAMPLE_NAME, $tokenFamily->name);
        $this->assertSame(self::SAMPLE_DESCRIPTION, $tokenFamily->description);
        $this->assertSame(self::SAMPLE_DESCRIPTION_I18N, $tokenFamily->description_i18n);
        $this->assertCount(1, $tokenFamily->keys);
        $this->assertTrue($tokenFamily->critical);
    }
} 
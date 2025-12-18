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
use Taler\Api\Dto\Location;
use Taler\Api\Dto\RelativeTime;
use Taler\Api\Order\Dto\Merchant;
use Taler\Api\Order\Dto\Exchange;
use Taler\Api\Inventory\Dto\Product;

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
    private const SAMPLE_SUMMARY = 'Test purchase';
    private const SAMPLE_ORDER_ID = 'order123';
    private const SAMPLE_MERCHANT_PUB = 'merchant_pub_key';
    private const SAMPLE_MERCHANT_BASE_URL = 'https://merchant.test/';
    private const SAMPLE_H_WIRE = 'h_wire_hash';
    private const SAMPLE_WIRE_METHOD = 'wire_method';
    private const SAMPLE_NONCE = 'nonce123';

    /** @var array{t_s: int} */
    private array $sampleTimestamp;

    /** @var array{description: string, product_id: string, quantity: int, unit: string, price: string} */
    private array $sampleProduct;

    /** @var array{name: string, email: string, website: string} */
    private array $sampleMerchant;

    /** @var array{url: string, priority: int, master_pub: string} */
    private array $sampleExchange;

    /** @var array{country: string, town: string} */
    private array $sampleLocation;

    private ContractChoice $sampleChoice;
    private ContractTokenFamily $sampleTokenFamily;

    protected function setUp(): void
    {
        $this->sampleTimestamp = ['t_s' => time()];
        $this->sampleProduct = [
            'description' => 'Test Product',
            'product_id' => 'prod123',
            'quantity' => 1,
            'unit' => 'piece',
            'price' => 'EUR:10'
        ];
        $this->sampleMerchant = [
            'name' => 'Test Merchant',
            'email' => 'merchant@test.com',
            'website' => 'https://merchant.test'
        ];
        $this->sampleExchange = [
            'url' => 'https://exchange.test',
            'priority' => 1,
            'master_pub' => 'master_pub_key'
        ];
        $this->sampleLocation = [
            'country' => 'Test Country',
            'town' => 'Test Town'
        ];

        // Setup ContractChoice
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

        $this->sampleChoice = new ContractChoice(
            amount: self::SAMPLE_AMOUNT,
            inputs: $inputs,
            outputs: $outputs,
            max_fee: self::SAMPLE_MAX_FEE
        );

        // Setup ContractTokenFamily
        $validityStart = new Timestamp(self::SAMPLE_VALIDITY_START_S);
        $validityEnd = new Timestamp(self::SAMPLE_VALIDITY_END_S);

        $rsaKey = new TokenIssueRsaPublicKey(
            rsa_pub: self::SAMPLE_RSA_PUB,
            signature_validity_start: $validityStart,
            signature_validity_end: $validityEnd
        );

        $details = new ContractSubscriptionTokenDetails(
            trusted_domains: self::SAMPLE_TRUSTED_DOMAINS
        );

        $this->sampleTokenFamily = new ContractTokenFamily(
            name: self::SAMPLE_NAME,
            description: self::SAMPLE_DESCRIPTION,
            description_i18n: self::SAMPLE_DESCRIPTION_I18N,
            keys: [$rsaKey],
            details: $details,
            critical: true
        );
    }

    public function testConstruct(): void
    {
        $contractTerms = new ContractTermsV1(
            choices: [$this->sampleChoice],
            token_families: [self::SAMPLE_TOKEN_FAMILY_SLUG => $this->sampleTokenFamily],
            summary: self::SAMPLE_SUMMARY,
            order_id: self::SAMPLE_ORDER_ID,
            products: [Product::createFromArray($this->sampleProduct)],
            timestamp: Timestamp::createFromArray($this->sampleTimestamp),
            refund_deadline: Timestamp::createFromArray($this->sampleTimestamp),
            pay_deadline: Timestamp::createFromArray($this->sampleTimestamp),
            wire_transfer_deadline: Timestamp::createFromArray($this->sampleTimestamp),
            merchant_pub: self::SAMPLE_MERCHANT_PUB,
            merchant_base_url: self::SAMPLE_MERCHANT_BASE_URL,
            merchant: Merchant::createFromArray($this->sampleMerchant),
            h_wire: self::SAMPLE_H_WIRE,
            wire_method: self::SAMPLE_WIRE_METHOD,
            exchanges: [Exchange::createFromArray($this->sampleExchange)],
            nonce: self::SAMPLE_NONCE
        );

        $this->assertCount(1, $contractTerms->choices);
        $this->assertInstanceOf(ContractChoice::class, $contractTerms->choices[0]);
        $this->assertCount(1, $contractTerms->token_families);
        $this->assertInstanceOf(ContractTokenFamily::class, $contractTerms->token_families[self::SAMPLE_TOKEN_FAMILY_SLUG]);
        $this->assertSame(self::SAMPLE_SUMMARY, $contractTerms->summary);
        $this->assertSame(self::SAMPLE_ORDER_ID, $contractTerms->order_id);
        $this->assertCount(1, $contractTerms->products);
        $this->assertInstanceOf(Product::class, $contractTerms->products[0]);
        $this->assertInstanceOf(Timestamp::class, $contractTerms->timestamp);
        $this->assertInstanceOf(Timestamp::class, $contractTerms->refund_deadline);
        $this->assertInstanceOf(Timestamp::class, $contractTerms->pay_deadline);
        $this->assertInstanceOf(Timestamp::class, $contractTerms->wire_transfer_deadline);
        $this->assertSame(self::SAMPLE_MERCHANT_PUB, $contractTerms->merchant_pub);
        $this->assertSame(self::SAMPLE_MERCHANT_BASE_URL, $contractTerms->merchant_base_url);
        $this->assertInstanceOf(Merchant::class, $contractTerms->merchant);
        $this->assertSame(self::SAMPLE_H_WIRE, $contractTerms->h_wire);
        $this->assertSame(self::SAMPLE_WIRE_METHOD, $contractTerms->wire_method);
        $this->assertCount(1, $contractTerms->exchanges);
        $this->assertInstanceOf(Exchange::class, $contractTerms->exchanges[0]);
        $this->assertSame(self::SAMPLE_NONCE, $contractTerms->nonce);
        $this->assertSame(1, $contractTerms->getVersion());
    }

    public function testConstructWithOptionalParameters(): void
    {
        $contractTerms = new ContractTermsV1(
            choices: [$this->sampleChoice],
            token_families: [self::SAMPLE_TOKEN_FAMILY_SLUG => $this->sampleTokenFamily],
            summary: self::SAMPLE_SUMMARY,
            order_id: self::SAMPLE_ORDER_ID,
            products: [Product::createFromArray($this->sampleProduct)],
            timestamp: Timestamp::createFromArray($this->sampleTimestamp),
            refund_deadline: Timestamp::createFromArray($this->sampleTimestamp),
            pay_deadline: Timestamp::createFromArray($this->sampleTimestamp),
            wire_transfer_deadline: Timestamp::createFromArray($this->sampleTimestamp),
            merchant_pub: self::SAMPLE_MERCHANT_PUB,
            merchant_base_url: self::SAMPLE_MERCHANT_BASE_URL,
            merchant: Merchant::createFromArray($this->sampleMerchant),
            h_wire: self::SAMPLE_H_WIRE,
            wire_method: self::SAMPLE_WIRE_METHOD,
            exchanges: [Exchange::createFromArray($this->sampleExchange)],
            nonce: self::SAMPLE_NONCE,
            summary_i18n: ['de' => 'Test Kauf'],
            public_reorder_url: 'https://reorder.test',
            fulfillment_url: 'https://fulfill.test',
            fulfillment_message: 'Thank you',
            fulfillment_message_i18n: ['de' => 'Danke'],
            delivery_location: Location::createFromArray($this->sampleLocation),
            delivery_date: Timestamp::createFromArray($this->sampleTimestamp),
            auto_refund: RelativeTime::createFromArray(['d_us' => 86400000000]),
            extra: (object)['custom' => 'value'],
            minimum_age: 18
        );

        $this->assertSame(['de' => 'Test Kauf'], $contractTerms->summary_i18n);
        $this->assertSame('https://reorder.test', $contractTerms->public_reorder_url);
        $this->assertSame('https://fulfill.test', $contractTerms->fulfillment_url);
        $this->assertSame('Thank you', $contractTerms->fulfillment_message);
        $this->assertSame(['de' => 'Danke'], $contractTerms->fulfillment_message_i18n);
        $this->assertInstanceOf(Location::class, $contractTerms->delivery_location);
        $this->assertInstanceOf(Timestamp::class, $contractTerms->delivery_date);
        $this->assertInstanceOf(RelativeTime::class, $contractTerms->auto_refund);
        $this->assertIsObject($contractTerms->extra);
        $this->assertSame(18, $contractTerms->minimum_age);
    }

    public function testCreateFromArray(): void
    {
        /** @var array{
         *   choices: array<int, array{
         *     amount: string,
         *     inputs: array<int, array{
         *       token_family_slug: string,
         *       count?: int|null
         *     }>,
         *     outputs: array<int, array{
         *       token_family_slug?: string,
         *       key_index?: int,
         *       count?: int|null,
         *       donau_urls?: array<int, string>,
         *       amount?: string
         *     }>,
         *     max_fee: string
         *   }>,
         *   token_families: array<string, array{
         *     name: string,
         *     description: string,
         *     description_i18n?: array<string, string>|null,
         *     keys: array<int, array{
         *       cipher: 'CS'|'RSA',
         *       rsa_pub?: string,
         *       cs_pub?: string,
         *       signature_validity_start: array{t_s: int|string},
         *       signature_validity_end: array{t_s: int|string}
         *     }>,
         *     details: array{
         *       class: 'subscription'|'discount',
         *       trusted_domains?: array<int, string>,
         *       expected_domains?: array<int, string>
         *     },
         *     critical: bool
         *   }>,
         *   summary: string,
         *   order_id: string,
         *   products: array<int, array{
         *     description: string,
         *     product_id?: string|null,
         *     description_i18n?: array<string, string>|null,
         *     quantity?: int|null,
         *     unit?: string|null,
         *     price?: string|null,
         *     image?: string|null,
         *     taxes?: array<int, array{name: string, tax: string}>|null,
         *     delivery_date?: array{t_s: int|string}|null
         *   }>,
         *   timestamp: array{t_s: int|string},
         *   refund_deadline: array{t_s: int|string},
         *   pay_deadline: array{t_s: int|string},
         *   wire_transfer_deadline: array{t_s: int|string},
         *   merchant_pub: string,
         *   merchant_base_url: string,
         *   merchant: array{
         *     name: string,
         *     email?: string|null,
         *     website?: string|null,
         *     logo?: string|null,
         *     address?: array{
         *       country?: string|null,
         *       town?: string|null,
         *       state?: string|null,
         *       region?: string|null,
         *       province?: string|null,
         *       street?: string|null
         *     }|null,
         *     jurisdiction?: array{
         *       country?: string|null,
         *       town?: string|null,
         *       state?: string|null,
         *       region?: string|null,
         *       province?: string|null,
         *       street?: string|null
         *     }|null
         *   },
         *   h_wire: string,
         *   wire_method: string,
         *   exchanges: array<int, array{
         *     url: string,
         *     priority: int,
         *     master_pub: string,
         *     max_contribution?: string|null
         *   }>,
         *   nonce: string,
         *   summary_i18n?: array<string, string>|null,
         *   public_reorder_url?: string|null,
         *   fulfillment_url?: string|null,
         *   fulfillment_message?: string|null,
         *   fulfillment_message_i18n?: array<string, string>|null,
         *   delivery_location?: array{
         *     country?: string|null,
         *     town?: string|null,
         *     state?: string|null,
         *     region?: string|null,
         *     province?: string|null,
         *     street?: string|null
         *   }|null,
         *   delivery_date?: array{t_s: int|string}|null,
         *   auto_refund?: array{d_us: int|string}|null,
         *   extra?: object|null,
         *   minimum_age?: int|null
         * } */
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
            ],
            'summary' => self::SAMPLE_SUMMARY,
            'order_id' => self::SAMPLE_ORDER_ID,
            'products' => [$this->sampleProduct],
            'timestamp' => $this->sampleTimestamp,
            'refund_deadline' => $this->sampleTimestamp,
            'pay_deadline' => $this->sampleTimestamp,
            'wire_transfer_deadline' => $this->sampleTimestamp,
            'merchant_pub' => self::SAMPLE_MERCHANT_PUB,
            'merchant_base_url' => self::SAMPLE_MERCHANT_BASE_URL,
            'merchant' => $this->sampleMerchant,
            'h_wire' => self::SAMPLE_H_WIRE,
            'wire_method' => self::SAMPLE_WIRE_METHOD,
            'exchanges' => [$this->sampleExchange],
            'nonce' => self::SAMPLE_NONCE,
            'summary_i18n' => ['de' => 'Test Kauf'],
            'public_reorder_url' => 'https://reorder.test',
            'fulfillment_url' => 'https://fulfill.test',
            'fulfillment_message' => 'Thank you',
            'fulfillment_message_i18n' => ['de' => 'Danke'],
            'delivery_location' => $this->sampleLocation,
            'delivery_date' => $this->sampleTimestamp,
            'auto_refund' => ['d_us' => 86400000000],
            'extra' => (object)['custom' => 'value'],
            'minimum_age' => 18
        ];

        $contractTerms = ContractTermsV1::createFromArray($data);

        // Test required parameters
        $this->assertCount(1, $contractTerms->choices);
        $this->assertInstanceOf(ContractChoice::class, $contractTerms->choices[0]);
        $this->assertCount(1, $contractTerms->token_families);
        $this->assertInstanceOf(ContractTokenFamily::class, $contractTerms->token_families[self::SAMPLE_TOKEN_FAMILY_SLUG]);
        $this->assertSame(self::SAMPLE_SUMMARY, $contractTerms->summary);
        $this->assertSame(self::SAMPLE_ORDER_ID, $contractTerms->order_id);
        $this->assertCount(1, $contractTerms->products);
        $this->assertInstanceOf(Product::class, $contractTerms->products[0]);
        $this->assertInstanceOf(Timestamp::class, $contractTerms->timestamp);
        $this->assertInstanceOf(Timestamp::class, $contractTerms->refund_deadline);
        $this->assertInstanceOf(Timestamp::class, $contractTerms->pay_deadline);
        $this->assertInstanceOf(Timestamp::class, $contractTerms->wire_transfer_deadline);
        $this->assertSame(self::SAMPLE_MERCHANT_PUB, $contractTerms->merchant_pub);
        $this->assertSame(self::SAMPLE_MERCHANT_BASE_URL, $contractTerms->merchant_base_url);
        $this->assertInstanceOf(Merchant::class, $contractTerms->merchant);
        $this->assertSame(self::SAMPLE_H_WIRE, $contractTerms->h_wire);
        $this->assertSame(self::SAMPLE_WIRE_METHOD, $contractTerms->wire_method);
        $this->assertCount(1, $contractTerms->exchanges);
        $this->assertInstanceOf(Exchange::class, $contractTerms->exchanges[0]);
        $this->assertSame(self::SAMPLE_NONCE, $contractTerms->nonce);

        // Test optional parameters
        $this->assertSame(['de' => 'Test Kauf'], $contractTerms->summary_i18n);
        $this->assertSame('https://reorder.test', $contractTerms->public_reorder_url);
        $this->assertSame('https://fulfill.test', $contractTerms->fulfillment_url);
        $this->assertSame('Thank you', $contractTerms->fulfillment_message);
        $this->assertSame(['de' => 'Danke'], $contractTerms->fulfillment_message_i18n);
        $this->assertInstanceOf(Location::class, $contractTerms->delivery_location);
        $this->assertInstanceOf(Timestamp::class, $contractTerms->delivery_date);
        $this->assertInstanceOf(RelativeTime::class, $contractTerms->auto_refund);
        $this->assertIsObject($contractTerms->extra);
        $this->assertSame(18, $contractTerms->minimum_age);
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
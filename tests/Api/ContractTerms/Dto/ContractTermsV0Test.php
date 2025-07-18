<?php

namespace Taler\Tests\Api\ContractTerms\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\ContractTerms\Dto\ContractTermsV0;
use Taler\Api\Dto\Location;
use Taler\Api\Dto\RelativeTime;
use Taler\Api\Dto\Timestamp;
use Taler\Api\Order\Dto\Merchant;
use Taler\Api\Order\Dto\Exchange;
use Taler\Api\Inventory\Dto\Product;

class ContractTermsV0Test extends TestCase
{
    private const SAMPLE_AMOUNT = 'TALER:10';
    private const SAMPLE_MAX_FEE = 'TALER:0.5';
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

    protected function setUp(): void
    {
        $this->sampleTimestamp = ['t_s' => time()];
        $this->sampleProduct = [
            'description' => 'Test Product',
            'product_id' => 'prod123',
            'quantity' => 1,
            'unit' => 'piece',
            'price' => 'TALER:10'
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
    }

    public function testConstruct(): void
    {
        $contractTerms = new ContractTermsV0(
            amount: self::SAMPLE_AMOUNT,
            max_fee: self::SAMPLE_MAX_FEE,
            summary: self::SAMPLE_SUMMARY,
            order_id: self::SAMPLE_ORDER_ID,
            products: [Product::fromArray($this->sampleProduct)],
            timestamp: Timestamp::fromArray($this->sampleTimestamp),
            refund_deadline: Timestamp::fromArray($this->sampleTimestamp),
            pay_deadline: Timestamp::fromArray($this->sampleTimestamp),
            wire_transfer_deadline: Timestamp::fromArray($this->sampleTimestamp),
            merchant_pub: self::SAMPLE_MERCHANT_PUB,
            merchant_base_url: self::SAMPLE_MERCHANT_BASE_URL,
            merchant: Merchant::fromArray($this->sampleMerchant),
            h_wire: self::SAMPLE_H_WIRE,
            wire_method: self::SAMPLE_WIRE_METHOD,
            exchanges: [Exchange::fromArray($this->sampleExchange)],
            nonce: self::SAMPLE_NONCE
        );

        $this->assertSame(self::SAMPLE_AMOUNT, $contractTerms->amount);
        $this->assertSame(self::SAMPLE_MAX_FEE, $contractTerms->max_fee);
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
        $this->assertSame(0, $contractTerms->version);
    }

    public function testConstructWithOptionalParameters(): void
    {
        $contractTerms = new ContractTermsV0(
            amount: self::SAMPLE_AMOUNT,
            max_fee: self::SAMPLE_MAX_FEE,
            summary: self::SAMPLE_SUMMARY,
            order_id: self::SAMPLE_ORDER_ID,
            products: [Product::fromArray($this->sampleProduct)],
            timestamp: Timestamp::fromArray($this->sampleTimestamp),
            refund_deadline: Timestamp::fromArray($this->sampleTimestamp),
            pay_deadline: Timestamp::fromArray($this->sampleTimestamp),
            wire_transfer_deadline: Timestamp::fromArray($this->sampleTimestamp),
            merchant_pub: self::SAMPLE_MERCHANT_PUB,
            merchant_base_url: self::SAMPLE_MERCHANT_BASE_URL,
            merchant: Merchant::fromArray($this->sampleMerchant),
            h_wire: self::SAMPLE_H_WIRE,
            wire_method: self::SAMPLE_WIRE_METHOD,
            exchanges: [Exchange::fromArray($this->sampleExchange)],
            nonce: self::SAMPLE_NONCE,
            summary_i18n: ['de' => 'Test Kauf'],
            public_reorder_url: 'https://reorder.test',
            fulfillment_url: 'https://fulfill.test',
            fulfillment_message: 'Thank you',
            fulfillment_message_i18n: ['de' => 'Danke'],
            delivery_location: Location::fromArray($this->sampleLocation),
            delivery_date: Timestamp::fromArray($this->sampleTimestamp),
            auto_refund: RelativeTime::fromArray(['d_us' => 86400000000]),
            extra: (object)['custom' => 'value'],
            minimum_age: 18,
            version: 0
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
        $this->assertSame(0, $contractTerms->version);
    }

    public function testCreateFromArrayWithRequiredParameters(): void
    {
        /** @var array{
         *   amount: string,
         *   max_fee: string,
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
         *   nonce: string
         * } */
        $data = [
            'amount' => self::SAMPLE_AMOUNT,
            'max_fee' => self::SAMPLE_MAX_FEE,
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
            'nonce' => self::SAMPLE_NONCE
        ];

        $contractTerms = ContractTermsV0::createFromArray($data);

        $this->assertSame(self::SAMPLE_AMOUNT, $contractTerms->amount);
        $this->assertSame(self::SAMPLE_MAX_FEE, $contractTerms->max_fee);
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
        $this->assertSame(0, $contractTerms->version);
    }

    public function testCreateFromArrayWithAllParameters(): void
    {
        /** @var array{
         *   amount: string,
         *   max_fee: string,
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
         *   minimum_age?: int|null,
         *   version?: int|null
         * } */
        $data = [
            'amount' => self::SAMPLE_AMOUNT,
            'max_fee' => self::SAMPLE_MAX_FEE,
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
            'minimum_age' => 18,
            'version' => 0
        ];

        $contractTerms = ContractTermsV0::createFromArray($data);

        $this->assertSame(self::SAMPLE_AMOUNT, $contractTerms->amount);
        $this->assertSame(self::SAMPLE_MAX_FEE, $contractTerms->max_fee);
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
        $this->assertSame(0, $contractTerms->version);
    }
} 
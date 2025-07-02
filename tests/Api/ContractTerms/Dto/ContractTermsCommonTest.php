<?php

namespace Taler\Tests\Api\ContractTerms\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\ContractTerms\Dto\ContractTermsCommon;
use Taler\Api\Dto\Location;
use Taler\Api\Dto\RelativeTime;
use Taler\Api\Dto\Timestamp;
use Taler\Api\Inventory\Dto\Product;
use Taler\Api\Order\Dto\Exchange;
use Taler\Api\Order\Dto\Merchant;

class ContractTermsCommonTest extends TestCase
{
    private const SAMPLE_SUMMARY = 'Test purchase';
    private const SAMPLE_ORDER_ID = 'test-123';
    private const SAMPLE_MERCHANT_PUB = 'EDDSAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';
    private const SAMPLE_MERCHANT_BASE_URL = 'https://example.com/';
    private const SAMPLE_H_WIRE = 'HASHAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';
    private const SAMPLE_WIRE_METHOD = 'x-taler-bank';
    private const SAMPLE_NONCE = 'NONCEAAAAAAAAAAAAAAAAAAAAAAAAAAAA';
    private const SAMPLE_TIMESTAMP_S = 1234567890;

    public function testConstruct(): void
    {
        $timestamp = new Timestamp(self::SAMPLE_TIMESTAMP_S);
        $product = new Product(description: 'Test product');
        $merchant = new Merchant(name: 'Test Merchant');
        $exchange = new Exchange(
            url: 'https://exchange.example.com/',
            priority: 1,
            master_pub: 'MASTERAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'
        );

        $contractTerms = new ContractTermsCommon(
            summary: self::SAMPLE_SUMMARY,
            order_id: self::SAMPLE_ORDER_ID,
            products: [$product],
            timestamp: $timestamp,
            refund_deadline: $timestamp,
            pay_deadline: $timestamp,
            wire_transfer_deadline: $timestamp,
            merchant_pub: self::SAMPLE_MERCHANT_PUB,
            merchant_base_url: self::SAMPLE_MERCHANT_BASE_URL,
            merchant: $merchant,
            h_wire: self::SAMPLE_H_WIRE,
            wire_method: self::SAMPLE_WIRE_METHOD,
            exchanges: [$exchange],
            nonce: self::SAMPLE_NONCE,
            summary_i18n: null,
            public_reorder_url: null,
            fulfillment_url: null,
            fulfillment_message: null,
            fulfillment_message_i18n: null,
            delivery_location: null,
            delivery_date: null,
            auto_refund: null,
            extra: null,
            minimum_age: null
        );

        $this->assertSame(self::SAMPLE_SUMMARY, $contractTerms->summary);
        $this->assertSame(self::SAMPLE_ORDER_ID, $contractTerms->order_id);
        $this->assertNull($contractTerms->summary_i18n);
        $this->assertNull($contractTerms->public_reorder_url);
        $this->assertNull($contractTerms->fulfillment_url);
        $this->assertNull($contractTerms->fulfillment_message);
        $this->assertNull($contractTerms->fulfillment_message_i18n);
        $this->assertCount(1, $contractTerms->products);
        $this->assertInstanceOf(Product::class, $contractTerms->products[0]);
        $this->assertSame($timestamp, $contractTerms->timestamp);
        $this->assertSame($timestamp, $contractTerms->refund_deadline);
        $this->assertSame($timestamp, $contractTerms->pay_deadline);
        $this->assertSame($timestamp, $contractTerms->wire_transfer_deadline);
        $this->assertSame(self::SAMPLE_MERCHANT_PUB, $contractTerms->merchant_pub);
        $this->assertSame(self::SAMPLE_MERCHANT_BASE_URL, $contractTerms->merchant_base_url);
        $this->assertInstanceOf(Merchant::class, $contractTerms->merchant);
        $this->assertSame(self::SAMPLE_H_WIRE, $contractTerms->h_wire);
        $this->assertSame(self::SAMPLE_WIRE_METHOD, $contractTerms->wire_method);
        $this->assertCount(1, $contractTerms->exchanges);
        $this->assertInstanceOf(Exchange::class, $contractTerms->exchanges[0]);
        $this->assertNull($contractTerms->delivery_location);
        $this->assertNull($contractTerms->delivery_date);
        $this->assertSame(self::SAMPLE_NONCE, $contractTerms->nonce);
        $this->assertNull($contractTerms->auto_refund);
        $this->assertNull($contractTerms->extra);
        $this->assertNull($contractTerms->minimum_age);
    }

    public function testFromArrayWithValidData(): void
    {
        $data = [
            'summary' => self::SAMPLE_SUMMARY,
            'order_id' => self::SAMPLE_ORDER_ID,
            'products' => [
                [
                    'description' => 'Test product'
                ]
            ],
            'timestamp' => ['t_s' => self::SAMPLE_TIMESTAMP_S],
            'refund_deadline' => ['t_s' => self::SAMPLE_TIMESTAMP_S],
            'pay_deadline' => ['t_s' => self::SAMPLE_TIMESTAMP_S],
            'wire_transfer_deadline' => ['t_s' => self::SAMPLE_TIMESTAMP_S],
            'merchant_pub' => self::SAMPLE_MERCHANT_PUB,
            'merchant_base_url' => self::SAMPLE_MERCHANT_BASE_URL,
            'merchant' => [
                'name' => 'Test Merchant'
            ],
            'h_wire' => self::SAMPLE_H_WIRE,
            'wire_method' => self::SAMPLE_WIRE_METHOD,
            'exchanges' => [
                [
                    'url' => 'https://exchange.example.com/',
                    'priority' => 1,
                    'master_pub' => 'MASTERAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'
                ]
            ],
            'nonce' => self::SAMPLE_NONCE,
            'summary_i18n' => [
                'en' => 'Test purchase',
                'de' => 'Testkauf'
            ],
            'public_reorder_url' => 'https://example.com/reorder/123',
            'fulfillment_url' => 'https://example.com/fulfill/123',
            'fulfillment_message' => 'Your purchase was successful',
            'fulfillment_message_i18n' => [
                'en' => 'Your purchase was successful',
                'de' => 'Ihr Kauf war erfolgreich'
            ],
            'delivery_location' => [
                'country' => 'DE',
                'city' => 'Berlin'
            ],
            'delivery_date' => ['t_s' => self::SAMPLE_TIMESTAMP_S],
            'auto_refund' => ['d_us' => 86400000000],
            'extra' => (object)['custom_field' => 'value'],
            'minimum_age' => 18
        ];

        $contractTerms = ContractTermsCommon::fromArray($data);

        $this->assertSame(self::SAMPLE_SUMMARY, $contractTerms->summary);
        $this->assertSame(self::SAMPLE_ORDER_ID, $contractTerms->order_id);
        $this->assertSame(['en' => 'Test purchase', 'de' => 'Testkauf'], $contractTerms->summary_i18n);
        $this->assertSame('https://example.com/reorder/123', $contractTerms->public_reorder_url);
        $this->assertSame('https://example.com/fulfill/123', $contractTerms->fulfillment_url);
        $this->assertSame('Your purchase was successful', $contractTerms->fulfillment_message);
        $this->assertSame(
            ['en' => 'Your purchase was successful', 'de' => 'Ihr Kauf war erfolgreich'],
            $contractTerms->fulfillment_message_i18n
        );
        $this->assertCount(1, $contractTerms->products);
        $this->assertInstanceOf(Product::class, $contractTerms->products[0]);
        $this->assertInstanceOf(Timestamp::class, $contractTerms->timestamp);
        $this->assertSame(self::SAMPLE_TIMESTAMP_S, $contractTerms->timestamp->t_s);
        $this->assertInstanceOf(Timestamp::class, $contractTerms->refund_deadline);
        $this->assertSame(self::SAMPLE_TIMESTAMP_S, $contractTerms->refund_deadline->t_s);
        $this->assertInstanceOf(Timestamp::class, $contractTerms->pay_deadline);
        $this->assertSame(self::SAMPLE_TIMESTAMP_S, $contractTerms->pay_deadline->t_s);
        $this->assertInstanceOf(Timestamp::class, $contractTerms->wire_transfer_deadline);
        $this->assertSame(self::SAMPLE_TIMESTAMP_S, $contractTerms->wire_transfer_deadline->t_s);
        $this->assertSame(self::SAMPLE_MERCHANT_PUB, $contractTerms->merchant_pub);
        $this->assertSame(self::SAMPLE_MERCHANT_BASE_URL, $contractTerms->merchant_base_url);
        $this->assertInstanceOf(Merchant::class, $contractTerms->merchant);
        $this->assertSame(self::SAMPLE_H_WIRE, $contractTerms->h_wire);
        $this->assertSame(self::SAMPLE_WIRE_METHOD, $contractTerms->wire_method);
        $this->assertCount(1, $contractTerms->exchanges);
        $this->assertInstanceOf(Exchange::class, $contractTerms->exchanges[0]);
        $this->assertInstanceOf(Location::class, $contractTerms->delivery_location);
        $this->assertInstanceOf(Timestamp::class, $contractTerms->delivery_date);
        $this->assertSame(self::SAMPLE_TIMESTAMP_S, $contractTerms->delivery_date->t_s);
        $this->assertSame(self::SAMPLE_NONCE, $contractTerms->nonce);
        $this->assertInstanceOf(RelativeTime::class, $contractTerms->auto_refund);
        $this->assertIsObject($contractTerms->extra);
        $this->assertSame('value', $contractTerms->extra->custom_field); // @phpstan-ignore-line it's an object set for the test
        $this->assertSame(18, $contractTerms->minimum_age);
    }
} 
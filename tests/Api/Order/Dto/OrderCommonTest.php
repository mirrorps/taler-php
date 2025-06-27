<?php

namespace Taler\Tests\Api\Order\Dto;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\Location;
use Taler\Api\Inventory\Dto\Product;
use Taler\Api\Dto\RelativeTime;
use Taler\Api\Dto\Timestamp;
use Taler\Api\Order\Dto\OrderCommon;

class OrderCommonTest extends TestCase
{
    public function testFromArray(): void
    {
        $data = [
            'summary' => 'Test order',
            'summary_i18n' => [
                'en' => 'Test order',
                'de' => 'Testbestellung'
            ],
            'order_id' => 'test-123',
            'public_reorder_url' => 'https://example.com/reorder/123',
            'fulfillment_url' => 'https://example.com/fulfill/123',
            'fulfillment_message' => 'Your order has been processed',
            'fulfillment_message_i18n' => [
                'en' => 'Your order has been processed',
                'de' => 'Ihre Bestellung wurde bearbeitet'
            ],
            'minimum_age' => 18,
            'products' => [
                [
                    'description' => 'Test product',
                    'quantity' => 1,
                    'unit_price' => '10.00',
                    'delivery_date' => ['t_s' => 1234567890],
                ]
            ],
            'timestamp' => ['t_s' => 1234567890],
            'refund_deadline' => ['t_s' => 1234567890],
            'pay_deadline' => ['t_s' => 1234567890],
            'wire_transfer_deadline' => ['t_s' => 1234567891],
            'merchant_base_url' => 'https://example.com/',
            'delivery_location' => [
                'country' => 'DE',
                'city' => 'Berlin',
                'address' => 'Test Street 123',
                'post_code' => '12345'
            ],
            'delivery_date' => ['t_s' => 1234567891],
            'auto_refund' => ['d_us' => 86400000],
            'extra' => (object) ['custom_field' => 'value']
        ];

        $orderCommon = OrderCommon::fromArray($data);

        $this->assertInstanceOf(OrderCommon::class, $orderCommon);
        $this->assertSame('Test order', $orderCommon->summary);
        $this->assertSame(['en' => 'Test order', 'de' => 'Testbestellung'], $orderCommon->summary_i18n);
        $this->assertSame('test-123', $orderCommon->order_id);
        $this->assertSame('https://example.com/reorder/123', $orderCommon->public_reorder_url);
        $this->assertSame('https://example.com/fulfill/123', $orderCommon->fulfillment_url);
        $this->assertSame('Your order has been processed', $orderCommon->fulfillment_message);
        $this->assertSame(
            ['en' => 'Your order has been processed', 'de' => 'Ihre Bestellung wurde bearbeitet'],
            $orderCommon->fulfillment_message_i18n
        );
        $this->assertSame(18, $orderCommon->minimum_age);
        $this->assertIsArray($orderCommon->products);
        $this->assertInstanceOf(Product::class, $orderCommon->products[0]);
        $this->assertInstanceOf(Timestamp::class, $orderCommon->timestamp);
        $this->assertInstanceOf(Timestamp::class, $orderCommon->refund_deadline);
        $this->assertInstanceOf(Timestamp::class, $orderCommon->pay_deadline);
        $this->assertInstanceOf(Timestamp::class, $orderCommon->wire_transfer_deadline);
        $this->assertSame('https://example.com/', $orderCommon->merchant_base_url);
        $this->assertInstanceOf(Location::class, $orderCommon->delivery_location);
        $this->assertInstanceOf(Timestamp::class, $orderCommon->delivery_date);
        $this->assertInstanceOf(RelativeTime::class, $orderCommon->auto_refund);
        $this->assertIsObject($orderCommon->extra);
        $this->assertSame('value', $orderCommon->extra->custom_field); // @phpstan-ignore-line it's an object set for the test
    }

    public function testFromArrayWithMinimalData(): void
    {
        $data = [
            'summary' => 'Test order'
        ];

        $orderCommon = OrderCommon::fromArray($data);

        $this->assertInstanceOf(OrderCommon::class, $orderCommon);
        $this->assertSame('Test order', $orderCommon->summary);
        $this->assertNull($orderCommon->summary_i18n);
        $this->assertNull($orderCommon->order_id);
        $this->assertNull($orderCommon->public_reorder_url);
        $this->assertNull($orderCommon->fulfillment_url);
        $this->assertNull($orderCommon->fulfillment_message);
        $this->assertNull($orderCommon->fulfillment_message_i18n);
        $this->assertNull($orderCommon->minimum_age);
        $this->assertNull($orderCommon->products);
        $this->assertNull($orderCommon->timestamp);
        $this->assertNull($orderCommon->refund_deadline);
        $this->assertNull($orderCommon->pay_deadline);
        $this->assertNull($orderCommon->wire_transfer_deadline);
        $this->assertNull($orderCommon->merchant_base_url);
        $this->assertNull($orderCommon->delivery_location);
        $this->assertNull($orderCommon->delivery_date);
        $this->assertNull($orderCommon->auto_refund);
        $this->assertNull($orderCommon->extra);
    }

    /**
     * @param array<string, mixed> $data
     * @param string $expectedMessage
     * @dataProvider invalidDataProvider
     */
    public function testFromArrayWithInvalidData(array $data, string $expectedMessage): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        OrderCommon::fromArray($data);
    }

    /**
     * @return array<string, array{data: array<string, mixed>, message: string}>
     */
    public function invalidDataProvider(): array
    {
        return [
            'missing_summary' => [
                'data' => [],
                'message' => 'Summary is required and must be a non-empty string'
            ],
            'empty_summary' => [
                'data' => ['summary' => ''],
                'message' => 'Summary is required and must be a non-empty string'
            ],
            'invalid_summary_i18n' => [
                'data' => ['summary' => 'Test', 'summary_i18n' => 'not_an_array'],
                'message' => 'Summary i18n must be an array of strings'
            ],
            'invalid_order_id_type' => [
                'data' => ['summary' => 'Test', 'order_id' => 123],
                'message' => 'Order ID must be a string'
            ],
            'invalid_order_id_format' => [
                'data' => ['summary' => 'Test', 'order_id' => 'test@123'],
                'message' => 'Order ID can only contain A-Za-z0-9.:_- characters'
            ],
            'invalid_minimum_age' => [
                'data' => ['summary' => 'Test', 'minimum_age' => -1],
                'message' => 'Minimum age must be a positive integer'
            ],
            'invalid_products_type' => [
                'data' => ['summary' => 'Test', 'products' => 'not_an_array'],
                'message' => 'Products must be an array'
            ],
            'invalid_product_item' => [
                'data' => ['summary' => 'Test', 'products' => ['not_an_array']],
                'message' => 'Each product must be an array'
            ],
            'invalid_merchant_base_url_format' => [
                'data' => ['summary' => 'Test', 'merchant_base_url' => 'https://example.com'],
                'message' => 'Merchant base URL must be an absolute URL that ends with a slash'
            ],
            'invalid_merchant_base_url' => [
                'data' => ['summary' => 'Test', 'merchant_base_url' => 'not_a_url/'],
                'message' => 'Merchant base URL must be a valid URL'
            ]
        ];
    }
} 
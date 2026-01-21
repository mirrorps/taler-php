<?php

namespace Taler\Tests\Api\Order\Dto;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\Location;
use Taler\Api\Inventory\Dto\Product;
use Taler\Api\Dto\RelativeTime;
use Taler\Api\Dto\Timestamp;
use Taler\Api\Order\Dto\OrderV0;

class OrderV0Test extends TestCase
{
    public function testFromArray(): void
    {
        $data = [
                         'summary' => 'Test order',
             'amount' => 'EUR:10.00',
             'max_fee' => 'EUR:1.00',
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
            'delivery_date' => ['t_s' => time() + 3600],
            'auto_refund' => ['d_us' => 86400000],
            'extra' => (object) ['custom_field' => 'value']
        ];

        $OrderV0 = OrderV0::createFromArray($data);

        $this->assertInstanceOf(OrderV0::class, $OrderV0);
                 $this->assertSame('Test order', $OrderV0->summary);
         $this->assertSame('EUR:10.00', $OrderV0->amount);
         $this->assertSame('EUR:1.00', $OrderV0->max_fee);
         $this->assertSame(['en' => 'Test order', 'de' => 'Testbestellung'], $OrderV0->summary_i18n);
        $this->assertSame('test-123', $OrderV0->order_id);
        $this->assertSame('https://example.com/reorder/123', $OrderV0->public_reorder_url);
        $this->assertSame('https://example.com/fulfill/123', $OrderV0->fulfillment_url);
        $this->assertSame('Your order has been processed', $OrderV0->fulfillment_message);
        $this->assertSame(
            ['en' => 'Your order has been processed', 'de' => 'Ihre Bestellung wurde bearbeitet'],
            $OrderV0->fulfillment_message_i18n
        );
        $this->assertSame(18, $OrderV0->minimum_age);
        $this->assertIsArray($OrderV0->products);
        $this->assertInstanceOf(Product::class, $OrderV0->products[0]);
        $this->assertInstanceOf(Timestamp::class, $OrderV0->timestamp);
        $this->assertInstanceOf(Timestamp::class, $OrderV0->refund_deadline);
        $this->assertInstanceOf(Timestamp::class, $OrderV0->pay_deadline);
        $this->assertInstanceOf(Timestamp::class, $OrderV0->wire_transfer_deadline);
        $this->assertSame('https://example.com/', $OrderV0->merchant_base_url);
        $this->assertInstanceOf(Location::class, $OrderV0->delivery_location);
        $this->assertInstanceOf(Timestamp::class, $OrderV0->delivery_date);
        $this->assertInstanceOf(RelativeTime::class, $OrderV0->auto_refund);
        $this->assertIsObject($OrderV0->extra);
        $this->assertObjectHasProperty('custom_field', $OrderV0->extra);
        $this->assertSame('value', ((array) $OrderV0->extra)['custom_field']);
    }

    public function testFromArrayWithMinimalData(): void
    {
        $data = [
            'summary' => 'Test order',
            'amount' => 'EUR:10.00',
            'fulfillment_message' => 'ok'
        ];

        $OrderV0 = OrderV0::createFromArray($data);

        $this->assertInstanceOf(OrderV0::class, $OrderV0);
                 $this->assertSame('Test order', $OrderV0->summary);
         $this->assertSame('EUR:10.00', $OrderV0->amount);
         $this->assertNull($OrderV0->max_fee);
         $this->assertNull($OrderV0->summary_i18n);
        $this->assertNull($OrderV0->order_id);
        $this->assertNull($OrderV0->public_reorder_url);
        $this->assertNull($OrderV0->fulfillment_url);
        $this->assertSame('ok', $OrderV0->fulfillment_message);
        $this->assertNull($OrderV0->fulfillment_message_i18n);
        $this->assertNull($OrderV0->minimum_age);
        $this->assertNull($OrderV0->products);
        $this->assertNull($OrderV0->timestamp);
        $this->assertNull($OrderV0->refund_deadline);
        $this->assertNull($OrderV0->pay_deadline);
        $this->assertNull($OrderV0->wire_transfer_deadline);
        $this->assertNull($OrderV0->merchant_base_url);
        $this->assertNull($OrderV0->delivery_location);
        $this->assertNull($OrderV0->delivery_date);
        $this->assertNull($OrderV0->auto_refund);
        $this->assertNull($OrderV0->extra);
    }

    /**
     * @param array<string, mixed> $data
     * @param string $message
     * @dataProvider invalidDataProvider
     */
    public function testFromArrayWithInvalidData(array $data, string $message): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        OrderV0::createFromArray($data);
    }

    /**
     * @return array<string, array{data: array<string, mixed>, message: string}>
     */
    public static function invalidDataProvider(): array
    {
        return [
            'missing_summary' => [
                 'data' => ['amount' => 'EUR:10.00'],
                 'message' => 'Summary is required and must be a non-empty string'
             ],
             'missing_amount' => [
                 'data' => ['summary' => 'Test'],
                 'message' => 'Amount is required and must be a non-empty string'
             ],
             'empty_amount' => [
                 'data' => ['summary' => 'Test', 'amount' => ''],
                 'message' => 'Amount is required and must be a non-empty string'
             ],
            'empty_summary' => [
                'data' => ['summary' => '', 'amount' => 'EUR:10.00'],
                'message' => 'Summary is required and must be a non-empty string'
            ],
            'invalid_summary_i18n' => [
                'data' => ['summary' => 'Test', 'amount' => 'EUR:10.00', 'summary_i18n' => 'not_an_array'],
                'message' => 'Summary i18n must be an array of strings'
            ],
            'invalid_order_id_type' => [
                'data' => ['summary' => 'Test', 'amount' => 'EUR:10.00', 'order_id' => 123],
                'message' => 'Order ID must be a string'
            ],
            'invalid_order_id_format' => [
                'data' => ['summary' => 'Test', 'amount' => 'EUR:10.00', 'order_id' => 'test@123'],
                'message' => 'Order ID can only contain A-Za-z0-9.:_- characters'
            ],
            'invalid_minimum_age' => [
                'data' => ['summary' => 'Test', 'amount' => 'EUR:10.00', 'minimum_age' => -1],
                'message' => 'Minimum age must be a positive integer'
            ],
            'invalid_products_type' => [
                'data' => ['summary' => 'Test', 'amount' => 'EUR:10.00', 'products' => 'not_an_array'],
                'message' => 'Products must be an array'
            ],
            'invalid_product_item' => [
                'data' => ['summary' => 'Test', 'amount' => 'EUR:10.00', 'products' => ['not_an_array']],
                'message' => 'Each product must be an array'
            ],
            'invalid_merchant_base_url_format' => [
                'data' => ['summary' => 'Test', 'amount' => 'EUR:10.00', 'merchant_base_url' => 'https://example.com'],
                'message' => 'Merchant base URL must be an absolute URL that ends with a slash'
            ],
            'invalid_merchant_base_url' => [
                'data' => ['summary' => 'Test', 'amount' => 'EUR:10.00', 'merchant_base_url' => 'not_a_url/'],
                'message' => 'Merchant base URL must be a valid URL'
            ],
            'amount_without_currency' => [
                'data' => ['summary' => 'Test', 'amount' => '42', 'fulfillment_message' => 'ok'],
                'message' => 'Amount must be a valid Taler amount in the format CURRENCY:VALUE (e.g., "EUR:1.50")'
            ],
            'amount_with_invalid_currency' => [
                'data' => ['summary' => 'Test', 'amount' => 'EU:10.00', 'fulfillment_message' => 'ok'],
                'message' => 'Amount must be a valid Taler amount in the format CURRENCY:VALUE (e.g., "EUR:1.50")'
            ],
            'invalid_max_fee_format' => [
                'data' => ['summary' => 'Test', 'amount' => 'EUR:10.00', 'max_fee' => '1.00', 'fulfillment_message' => 'ok'],
                'message' => 'Max fee must be a valid Taler amount in the format CURRENCY:VALUE (e.g., "EUR:0.10")'
            ]
        ];
    }
} 
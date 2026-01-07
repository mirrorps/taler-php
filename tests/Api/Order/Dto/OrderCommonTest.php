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
            'delivery_date' => ['t_s' => time() + 3600],
            'auto_refund' => ['d_us' => 86400000],
            'extra' => (object) ['custom_field' => 'value']
        ];

        $OrderCommon = OrderCommon::createFromArray($data);

        $this->assertInstanceOf(OrderCommon::class, $OrderCommon);
        $this->assertSame('Test order', $OrderCommon->summary);
        $this->assertSame(['en' => 'Test order', 'de' => 'Testbestellung'], $OrderCommon->summary_i18n);
        $this->assertSame('test-123', $OrderCommon->order_id);
        $this->assertSame('https://example.com/reorder/123', $OrderCommon->public_reorder_url);
        $this->assertSame('https://example.com/fulfill/123', $OrderCommon->fulfillment_url);
        $this->assertSame('Your order has been processed', $OrderCommon->fulfillment_message);
        $this->assertSame([
                'en' => 'Your order has been processed', 
                'de' => 'Ihre Bestellung wurde bearbeitet'
            ],
            $OrderCommon->fulfillment_message_i18n
        );
        $this->assertSame(18, $OrderCommon->minimum_age);
        $this->assertIsArray($OrderCommon->products);
        $this->assertInstanceOf(Product::class, $OrderCommon->products[0]);
        $this->assertInstanceOf(Timestamp::class, $OrderCommon->timestamp);
        $this->assertInstanceOf(Timestamp::class, $OrderCommon->refund_deadline);
        $this->assertInstanceOf(Timestamp::class, $OrderCommon->pay_deadline);
        $this->assertInstanceOf(Timestamp::class, $OrderCommon->wire_transfer_deadline);
        $this->assertSame('https://example.com/', $OrderCommon->merchant_base_url);
        $this->assertInstanceOf(Location::class, $OrderCommon->delivery_location);
        $this->assertInstanceOf(Timestamp::class, $OrderCommon->delivery_date);
        $this->assertInstanceOf(RelativeTime::class, $OrderCommon->auto_refund);
        $this->assertIsObject($OrderCommon->extra);
        $this->assertObjectHasProperty('custom_field', $OrderCommon->extra);
        $this->assertSame('value', ((array) $OrderCommon->extra)['custom_field']);
    }

    public function testFromArrayWithMinimalData(): void
    {
        $data = [
            'summary' => 'Test order',
            // Must satisfy fulfillment requirement
            'fulfillment_message' => 'ok',
        ];

        $OrderCommon = OrderCommon::createFromArray($data);

        $this->assertInstanceOf(OrderCommon::class, $OrderCommon);
        $this->assertSame('Test order', $OrderCommon->summary);
        $this->assertNull($OrderCommon->summary_i18n);
        $this->assertNull($OrderCommon->order_id);
        $this->assertNull($OrderCommon->public_reorder_url);
        $this->assertNull($OrderCommon->fulfillment_url);
        $this->assertSame('ok', $OrderCommon->fulfillment_message);
        $this->assertNull($OrderCommon->fulfillment_message_i18n);
        $this->assertNull($OrderCommon->minimum_age);
        $this->assertNull($OrderCommon->products);
        $this->assertNull($OrderCommon->timestamp);
        $this->assertNull($OrderCommon->refund_deadline);
        $this->assertNull($OrderCommon->pay_deadline);
        $this->assertNull($OrderCommon->wire_transfer_deadline);
        $this->assertNull($OrderCommon->merchant_base_url);
        $this->assertNull($OrderCommon->delivery_location);
        $this->assertNull($OrderCommon->delivery_date);
        $this->assertNull($OrderCommon->auto_refund);
        $this->assertNull($OrderCommon->extra);
    }

    public function testMissingFulfillmentThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Either fulfillment_url or fulfillment_message must be specified');

        OrderCommon::createFromArray([
            'summary' => 'Test order without fulfillment'
        ]);
    }

    public function testFulfillmentMessageI18nOnlyIsAccepted(): void
    {
        $dto = OrderCommon::createFromArray([
            'summary' => 'Has i18n fulfillment only',
            'fulfillment_message_i18n' => ['en' => 'done']
        ]);

        $this->assertInstanceOf(OrderCommon::class, $dto);
        $this->assertNull($dto->fulfillment_message);
        $this->assertSame(['en' => 'done'], $dto->fulfillment_message_i18n);
    }

    public function testFulfillmentMessageI18nInvalidShapeThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Fulfillment message i18n must be an array of strings');

        OrderCommon::createFromArray([
            'summary' => 'Invalid i18n',
            'fulfillment_message_i18n' => ['en' => 123],
        ]);
    }

    public function testDeliveryDateMustBeInFuture(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Delivery date must be in the future');

        OrderCommon::createFromArray([
            'summary' => 'Past delivery',
            'fulfillment_message' => 'ok',
            'delivery_date' => ['t_s' => time() - 10],
        ]);
    }

    public function testWireTransferMustBeAfterRefund(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Wire transfer deadline must be after refund deadline');

        OrderCommon::createFromArray([
            'summary' => 'Bad deadlines',
            'fulfillment_message' => 'ok',
            'refund_deadline' => ['t_s' => 5_000],
            'wire_transfer_deadline' => ['t_s' => 4_000],
        ]);
    }

    public function testWireNeverAfterRefundIsAllowed(): void
    {
        $dto = OrderCommon::createFromArray([
            'summary' => 'Wire never okay',
            'fulfillment_message' => 'ok',
            'refund_deadline' => ['t_s' => 5_000],
            'wire_transfer_deadline' => ['t_s' => 'never'],
        ]);

        $this->assertInstanceOf(OrderCommon::class, $dto);
    }

    public function testRefundNeverMakesWireInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Wire transfer deadline must be after refund deadline');

        OrderCommon::createFromArray([
            'summary' => 'Refund never invalidates finite wire',
            'fulfillment_message' => 'ok',
            'refund_deadline' => ['t_s' => 'never'],
            'wire_transfer_deadline' => ['t_s' => 5_000],
        ]);
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

        OrderCommon::createFromArray($data);
    }

    /**
     * @return array<string, array{data: array<string, mixed>, message: string}>
     */
    public static function invalidDataProvider(): array
    {
        return [
            'missing_summary' => [
                 'data' => ['amount' => '10.00'],
                 'message' => 'Summary is required and must be a non-empty string'
             ],
            'empty_summary' => [
                'data' => ['summary' => ''],
                'message' => 'Summary is required and must be a non-empty string'
            ],
            'invalid_summary_i18n' => [
                'data' => ['summary' => 'Test', 'amount' => '10.00', 'summary_i18n' => 'not_an_array'],
                'message' => 'Summary i18n must be an array of strings'
            ],
            'invalid_order_id_type' => [
                'data' => ['summary' => 'Test', 'amount' => '10.00', 'order_id' => 123],
                'message' => 'Order ID must be a string'
            ],
            'invalid_order_id_format' => [
                'data' => ['summary' => 'Test', 'amount' => '10.00', 'order_id' => 'test@123'],
                'message' => 'Order ID can only contain A-Za-z0-9.:_- characters'
            ],
            'invalid_minimum_age' => [
                'data' => ['summary' => 'Test', 'amount' => '10.00', 'minimum_age' => -1],
                'message' => 'Minimum age must be a positive integer'
            ],
            'invalid_products_type' => [
                'data' => ['summary' => 'Test', 'amount' => '10.00', 'products' => 'not_an_array'],
                'message' => 'Products must be an array'
            ],
            'invalid_product_item' => [
                'data' => ['summary' => 'Test', 'amount' => '10.00', 'products' => ['not_an_array']],
                'message' => 'Each product must be an array'
            ],
            'invalid_merchant_base_url_format' => [
                'data' => ['summary' => 'Test', 'amount' => '10.00', 'merchant_base_url' => 'https://example.com'],
                'message' => 'Merchant base URL must be an absolute URL that ends with a slash'
            ],
            'invalid_merchant_base_url' => [
                'data' => ['summary' => 'Test', 'amount' => '10.00', 'merchant_base_url' => 'not_a_url/'],
                'message' => 'Merchant base URL must be a valid URL'
            ],
            'missing_fulfillment' => [
                'data' => ['summary' => 'Test'],
                'message' => 'Either fulfillment_url or fulfillment_message must be specified'
            ],
            'delivery_date_not_future' => [
                'data' => ['summary' => 'Test', 'fulfillment_message' => 'ok', 'delivery_date' => ['t_s' => 0]],
                'message' => 'Delivery date must be in the future'
            ],
            'wire_before_refund' => [
                'data' => [
                    'summary' => 'Test',
                    'fulfillment_message' => 'ok',
                    'refund_deadline' => ['t_s' => 2000],
                    'wire_transfer_deadline' => ['t_s' => 1999],
                ],
                'message' => 'Wire transfer deadline must be after refund deadline'
            ],
        ];
    }
} 
<?php

namespace Taler\Tests\Api\Order\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\RelativeTime;
use Taler\Api\Inventory\Dto\MinimalInventoryProduct;
use Taler\Api\Order\Dto\OrderV0;
use Taler\Api\Order\Dto\OrderV1;
use Taler\Api\Order\Dto\OrderChoice;
use Taler\Api\Order\Dto\PostOrderRequest;

/**
 * Test cases for PostOrderRequest DTO.
 */
class PostOrderRequestTest extends TestCase
{
    private OrderV0 $order_v0;
    private OrderV1 $order_v1;
    private RelativeTime $relative_time;
    private MinimalInventoryProduct $inventory_product;

    protected function setUp(): void
    {
        $this->order_v0 = new OrderV0(
            summary: 'Test Order V0',
            amount: '10.00'
        );

        $this->order_v1 = OrderV1::createFromArray([
            'version' => 1,
            'summary' => 'Test Order V1',
            'choices' => [
                [
                    'amount' => '10.00'
                ]
            ]
        ]);

        $this->relative_time = new RelativeTime(
            d_us: 86400000000 // 1 day in microseconds
        );

        $this->inventory_product = new MinimalInventoryProduct(
            product_id: 'test-product',
            quantity: 1
        );
    }

    /**
     * Test valid construction with minimal OrderV0 data.
     */
    public function testValidMinimalConstructionWithOrderV0(): void
    {
        $request = new PostOrderRequest($this->order_v0);
        
        $this->assertSame($this->order_v0, $request->order);
        $this->assertNull($request->refund_delay);
        $this->assertNull($request->payment_target);
        $this->assertNull($request->session_id);
        $this->assertNull($request->inventory_products);
        $this->assertNull($request->lock_uuids);
        $this->assertNull($request->create_token);
        $this->assertNull($request->otp_id);
    }

    /**
     * Test valid construction with minimal OrderV1 data.
     */
    public function testValidMinimalConstructionWithOrderV1(): void
    {
        $request = new PostOrderRequest($this->order_v1);
        
        $this->assertSame($this->order_v1, $request->order);
        $this->assertNull($request->refund_delay);
        $this->assertNull($request->payment_target);
        $this->assertNull($request->session_id);
        $this->assertNull($request->inventory_products);
        $this->assertNull($request->lock_uuids);
        $this->assertNull($request->create_token);
        $this->assertNull($request->otp_id);
    }

    /**
     * Test valid construction with all data using OrderV0.
     */
    public function testValidFullConstructionWithOrderV0(): void
    {
        $request = new PostOrderRequest(
            order: $this->order_v0,
            refund_delay: $this->relative_time,
            payment_target: 'target',
            session_id: 'session123',
            inventory_products: [$this->inventory_product],
            lock_uuids: ['uuid1', 'uuid2'],
            create_token: true,
            otp_id: 'otp123'
        );
        
        $this->assertSame($this->order_v0, $request->order);
        $this->assertSame($this->relative_time, $request->refund_delay);
        $this->assertSame('target', $request->payment_target);
        $this->assertSame('session123', $request->session_id);
        $this->assertSame([$this->inventory_product], $request->inventory_products);
        $this->assertSame(['uuid1', 'uuid2'], $request->lock_uuids);
        $this->assertTrue($request->create_token);
        $this->assertSame('otp123', $request->otp_id);
    }

    /**
     * Test valid construction with all data using OrderV1.
     */
    public function testValidFullConstructionWithOrderV1(): void
    {
        $request = new PostOrderRequest(
            order: $this->order_v1,
            refund_delay: $this->relative_time,
            payment_target: 'target',
            session_id: 'session123',
            inventory_products: [$this->inventory_product],
            lock_uuids: ['uuid1', 'uuid2'],
            create_token: true,
            otp_id: 'otp123'
        );
        
        $this->assertSame($this->order_v1, $request->order);
        $this->assertSame($this->relative_time, $request->refund_delay);
        $this->assertSame('target', $request->payment_target);
        $this->assertSame('session123', $request->session_id);
        $this->assertSame([$this->inventory_product], $request->inventory_products);
        $this->assertSame(['uuid1', 'uuid2'], $request->lock_uuids);
        $this->assertTrue($request->create_token);
        $this->assertSame('otp123', $request->otp_id);
    }

    /**
     * Test validation with empty inventory products array.
     */
    public function testValidationEmptyInventoryProducts(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Inventory products array cannot be empty when provided');
        
        new PostOrderRequest(
            order: $this->order_v0,
            inventory_products: []
        );
    }

    /**
     * Test validation with empty lock UUIDs array.
     */
    public function testValidationEmptyLockUuids(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Lock UUIDs array cannot be empty when provided');
        
        new PostOrderRequest(
            order: $this->order_v0,
            lock_uuids: []
        );
    }

    /**
     * Test skipping validation.
     */
    public function testSkipValidation(): void
    {
        $request = new PostOrderRequest(
            order: $this->order_v0,
            inventory_products: [],  // Would normally fail validation
            validate: false
        );
        
        $this->assertSame([], $request->inventory_products);
    }

    /**
     * Test createFromArray with minimal OrderV0 data.
     */
    public function testCreateFromArrayMinimalOrderV0(): void
    {
        $data = [
            'order' => [
                'summary' => 'Test Order V0',
                'amount' => '10.00'
            ]
        ];
        
        $request = PostOrderRequest::createFromArray($data);
        
        $this->assertInstanceOf(PostOrderRequest::class, $request);
        $this->assertInstanceOf(OrderV0::class, $request->order);
        $this->assertSame('Test Order V0', $request->order->summary);
    }

    /**
     * Test createFromArray with minimal OrderV1 data.
     */
    public function testCreateFromArrayMinimalOrderV1(): void
    {
        $data = [
            'order' => [
                'version' => 1,
                'summary' => 'Test Order V1',
                'choices' => [
                    [
                        'amount' => '10.00'
                    ]
                ]
            ]
        ];
        
        $request = PostOrderRequest::createFromArray($data);
        
        $this->assertInstanceOf(PostOrderRequest::class, $request);
        $this->assertInstanceOf(OrderV1::class, $request->order);
        $this->assertCount(1, $request->order->choices);
    }

    /**
     * Test createFromArray with full data using OrderV0.
     */
    public function testCreateFromArrayFullOrderV0(): void
    {
        $data = [
            'order' => [
                'summary' => 'Test Order V0',
                'amount' => '10.00'
            ],
            'refund_delay' => ['d_us' => 86400000000],
            'payment_target' => 'target',
            'session_id' => 'session123',
            'inventory_products' => [['product_id' => 'test-product', 'quantity' => 1]],
            'lock_uuids' => ['uuid1', 'uuid2'],
            'create_token' => true,
            'otp_id' => 'otp123'
        ];
        
        $request = PostOrderRequest::createFromArray($data);
        
        $this->assertInstanceOf(PostOrderRequest::class, $request);
        $this->assertInstanceOf(OrderV0::class, $request->order);
        $this->assertInstanceOf(RelativeTime::class, $request->refund_delay);
        $this->assertSame('target', $request->payment_target);
        $this->assertSame('session123', $request->session_id);
        $this->assertNotEmpty($request->inventory_products);
        $this->assertInstanceOf(MinimalInventoryProduct::class, $request->inventory_products[0]);
        $this->assertSame(['uuid1', 'uuid2'], $request->lock_uuids);
        $this->assertTrue($request->create_token);
        $this->assertSame('otp123', $request->otp_id);
    }

    /**
     * Test createFromArray with full data using OrderV1.
     */
    public function testCreateFromArrayFullOrderV1(): void
    {
        $data = [
            'order' => [
                'version' => 1,
                'summary' => 'Test Order V1',
                'choices' => [
                    [
                        'amount' => '10.00'
                    ]
                ]
            ],
            'refund_delay' => ['d_us' => 86400000000],
            'payment_target' => 'target',
            'session_id' => 'session123',
            'inventory_products' => [['product_id' => 'test-product', 'quantity' => 1]],
            'lock_uuids' => ['uuid1', 'uuid2'],
            'create_token' => true,
            'otp_id' => 'otp123'
        ];
        
        $request = PostOrderRequest::createFromArray($data);
        
        $this->assertInstanceOf(PostOrderRequest::class, $request);
        $this->assertInstanceOf(OrderV1::class, $request->order);
        $this->assertInstanceOf(RelativeTime::class, $request->refund_delay);
        $this->assertSame('target', $request->payment_target);
        $this->assertSame('session123', $request->session_id);
        $this->assertNotEmpty($request->inventory_products);
        $this->assertInstanceOf(MinimalInventoryProduct::class, $request->inventory_products[0]);
        $this->assertSame(['uuid1', 'uuid2'], $request->lock_uuids);
        $this->assertTrue($request->create_token);
        $this->assertSame('otp123', $request->otp_id);
    }
}
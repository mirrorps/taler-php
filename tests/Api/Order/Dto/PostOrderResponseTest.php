<?php

namespace Taler\Tests\Api\Order\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Order\Dto\PostOrderResponse;

class PostOrderResponseTest extends TestCase
{
    private const SAMPLE_ORDER_ID = 'test-order-123';
    private const SAMPLE_TOKEN = 'test-token-456';

    public function testConstruct(): void
    {
        $response = new PostOrderResponse(
            order_id: self::SAMPLE_ORDER_ID
        );

        $this->assertSame(self::SAMPLE_ORDER_ID, $response->order_id);
        $this->assertNull($response->token);
    }

    public function testConstructWithToken(): void
    {
        $response = new PostOrderResponse(
            order_id: self::SAMPLE_ORDER_ID,
            token: self::SAMPLE_TOKEN
        );

        $this->assertSame(self::SAMPLE_ORDER_ID, $response->order_id);
        $this->assertSame(self::SAMPLE_TOKEN, $response->token);
    }

    public function testConstructWithoutValidation(): void
    {
        $response = new PostOrderResponse(
            order_id: self::SAMPLE_ORDER_ID,
            validate: false
        );

        $this->assertSame(self::SAMPLE_ORDER_ID, $response->order_id);
        $this->assertNull($response->token);
    }

    public function testCreateFromArrayWithRequiredParameters(): void
    {
        $data = [
            'order_id' => self::SAMPLE_ORDER_ID
        ];

        $response = PostOrderResponse::createFromArray($data);

        $this->assertSame(self::SAMPLE_ORDER_ID, $response->order_id);
        $this->assertNull($response->token);
    }

    public function testCreateFromArrayWithAllParameters(): void
    {
        $data = [
            'order_id' => self::SAMPLE_ORDER_ID,
            'token' => self::SAMPLE_TOKEN
        ];

        $response = PostOrderResponse::createFromArray($data);

        $this->assertSame(self::SAMPLE_ORDER_ID, $response->order_id);
        $this->assertSame(self::SAMPLE_TOKEN, $response->token);
    }

    public function testValidationFailsWithEmptyOrderId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Order ID cannot be empty');

        new PostOrderResponse(order_id: '');
    }
}
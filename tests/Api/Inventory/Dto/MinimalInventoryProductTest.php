<?php

namespace Taler\Tests\Api\Inventory\Dto;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Taler\Api\Inventory\Dto\MinimalInventoryProduct;

class MinimalInventoryProductTest extends TestCase
{
    public function testValidConstruction(): void
    {
        $product = new MinimalInventoryProduct('test-product-123', 5);

        $this->assertSame('test-product-123', $product->getProductId());
        $this->assertSame(5, $product->getQuantity());
    }

    public function testToArray(): void
    {
        $product = new MinimalInventoryProduct('test-product-123', 5);
        $expected = [
            'product_id' => 'test-product-123',
            'quantity' => 5
        ];

        $this->assertSame($expected, $product->toArray());
    }

    /**
     * @param string $product_id
     * @param int $quantity
     * @param string $expectedMessage
     * @dataProvider invalidDataProvider
     */
    public function testInvalidConstruction(string $product_id, int $quantity, string $expectedMessage): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        new MinimalInventoryProduct($product_id, $quantity);
    }

    /**
     * @return array<string, array{product_id: string, quantity: int, message: string}>
     */
    public function invalidDataProvider(): array
    {
        return [
            'empty_product_id' => [
                'product_id' => '',
                'quantity' => 1,
                'message' => 'Product ID must be a non-empty string'
            ],
            'whitespace_product_id' => [
                'product_id' => '   ',
                'quantity' => 1,
                'message' => 'Product ID must be a non-empty string'
            ],
            'zero_quantity' => [
                'product_id' => 'test-123',
                'quantity' => 0,
                'message' => 'Quantity must be greater than zero'
            ],
            'negative_quantity' => [
                'product_id' => 'test-123',
                'quantity' => -1,
                'message' => 'Quantity must be greater than zero'
            ],
        ];
    }
} 
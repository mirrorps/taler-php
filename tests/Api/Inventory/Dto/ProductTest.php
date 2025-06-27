<?php

namespace Taler\Tests\Api\Inventory\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Inventory\Dto\Product;
use Taler\Api\Dto\Tax;
use Taler\Api\Dto\Timestamp;

class ProductTest extends TestCase
{
    public function testFromArray(): void
    {
        $data = [
            'product_id' => 'PROD123',
            'description' => 'Test Product',
            'description_i18n' => [
                'en' => 'Test Product',
                'de' => 'Test Produkt'
            ],
            'quantity' => 2,
            'unit' => 'pieces',
            'price' => '19.99',
            'image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
            'taxes' => [
                [
                    'name' => 'VAT',
                    'tax' => '3.99'
                ]
            ],
            'delivery_date' => [
                't_s' => 1234567890
            ]
        ];

        $product = Product::fromArray($data);

        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals($data['product_id'], $product->product_id);
        $this->assertEquals($data['description'], $product->description);
        $this->assertEquals($data['description_i18n'], $product->description_i18n);
        $this->assertEquals($data['quantity'], $product->quantity);
        $this->assertEquals($data['unit'], $product->unit);
        $this->assertEquals($data['price'], $product->price);
        $this->assertEquals($data['image'], $product->image);
        $this->assertCount(1, $product->taxes);
        $this->assertInstanceOf(Tax::class, $product->taxes[0]);
        $this->assertEquals($data['taxes'][0]['name'], $product->taxes[0]->name);
        $this->assertEquals($data['taxes'][0]['tax'], $product->taxes[0]->tax);
        $this->assertInstanceOf(Timestamp::class, $product->delivery_date);
        $this->assertEquals($data['delivery_date']['t_s'], $product->delivery_date->t_s);
    }

    public function testFromArrayWithMinimalData(): void
    {
        $data = [
            'description' => 'Minimal Product'
        ];

        $product = Product::fromArray($data);

        $this->assertInstanceOf(Product::class, $product);
        $this->assertNull($product->product_id);
        $this->assertEquals($data['description'], $product->description);
        $this->assertNull($product->description_i18n);
        $this->assertNull($product->quantity);
        $this->assertNull($product->unit);
        $this->assertNull($product->price);
        $this->assertNull($product->image);
        $this->assertNull($product->taxes);
        $this->assertNull($product->delivery_date);
    }

} 
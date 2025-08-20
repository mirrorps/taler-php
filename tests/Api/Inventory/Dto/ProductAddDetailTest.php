<?php

namespace Taler\Tests\Api\Inventory\Dto;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\Location;
use Taler\Api\Dto\Timestamp;
use Taler\Api\Inventory\Dto\ProductAddDetail;

class ProductAddDetailTest extends TestCase
{
    public function testValidConstruction(): void
    {
        $detail = new ProductAddDetail(
            product_id: 'prod-1',
            description: 'Desc',
            unit: 'kg',
            price: 'EUR:1.50',
            total_stock: 10,
            product_name: 'Name',
            description_i18n: ['en' => 'Desc'],
            categories: [1, 2],
            image: 'data:image/png;base64,AAAA',
            address: new Location(town: 'City'),
            next_restock: new Timestamp(1700000000),
            minimum_age: 18
        );

        $this->assertSame('prod-1', $detail->product_id);
        $this->assertSame('kg', $detail->unit);
        $this->assertSame('EUR:1.50', $detail->price);
        $this->assertSame(10, $detail->total_stock);
    }

    public function testCreateFromArray(): void
    {
        $detail = ProductAddDetail::createFromArray([
            'product_id' => 'p',
            'description' => 'd',
            'unit' => 'u',
            'price' => 'EUR:0',
            'total_stock' => -1,
            'address' => ['town' => 'City'],
            'next_restock' => ['t_s' => 0],
            'taxes' => [['name' => 'VAT', 'tax' => 'EUR:0.10']],
        ]);

        $this->assertSame('p', $detail->product_id);
        $this->assertSame('City', $detail->address?->town);
        $this->assertSame('VAT', $detail->taxes[0]->name);
        $this->assertSame(0, $detail->next_restock?->t_s);
    }

    public function testValidationFailures(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ProductAddDetail(product_id: '', description: 'd', unit: 'u', price: 'p', total_stock: 1);
    }
}



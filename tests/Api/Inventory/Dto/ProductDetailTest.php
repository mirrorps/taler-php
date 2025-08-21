<?php

namespace Taler\Tests\Api\Inventory\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Inventory\Dto\ProductDetail;

class ProductDetailTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = [
            'product_name' => 'Name',
            'description' => 'Desc',
            'description_i18n' => ['en' => 'Desc'],
            'unit' => 'kg',
            'categories' => [1, 2],
            'price' => 'EUR:1',
            'image' => 'data:image/png;base64,AAAA',
            'taxes' => [
                ['name' => 'VAT', 'tax' => 'EUR:0.10']
            ],
            'total_stock' => 10,
            'total_sold' => 5,
            'total_lost' => 0,
            'address' => ['town' => 'City'],
            'next_restock' => ['t_s' => 0],
            'minimum_age' => 18,
        ];

        $detail = ProductDetail::createFromArray($data);

        $this->assertSame('Name', $detail->product_name);
        $this->assertSame('kg', $detail->unit);
        $this->assertSame('EUR:1', $detail->price);
        $this->assertSame(10, $detail->total_stock);
        $this->assertSame(5, $detail->total_sold);
        $this->assertSame(0, $detail->total_lost);
        $this->assertSame('City', $detail->address?->town);
        $this->assertSame(0, $detail->next_restock?->t_s);
        $this->assertSame('Desc', $detail->description_i18n['en']);
    }
}



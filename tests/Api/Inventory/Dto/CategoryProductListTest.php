<?php

namespace Taler\Tests\Api\Inventory\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Inventory\Dto\CategoryProductList;
use Taler\Api\Inventory\Dto\CategoryProductSummary;

class CategoryProductListTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = [
            'name' => 'Beverages',
            'name_i18n' => [
                'en' => 'Beverages'
            ],
            'products' => [
                ['product_id' => 'prod-1'],
                ['product_id' => 'prod-2'],
            ]
        ];

        $list = CategoryProductList::createFromArray($data);

        $this->assertSame('Beverages', $list->name);
        $this->assertSame('Beverages', $list->name_i18n['en']);
        $this->assertCount(2, $list->products);
        $this->assertInstanceOf(CategoryProductSummary::class, $list->products[0]);
        $this->assertSame('prod-1', $list->products[0]->product_id);
    }
}



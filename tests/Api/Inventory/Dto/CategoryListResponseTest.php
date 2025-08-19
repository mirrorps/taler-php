<?php

namespace Taler\Tests\Api\Inventory\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Inventory\Dto\CategoryListEntry;
use Taler\Api\Inventory\Dto\CategoryListResponse;

class CategoryListResponseTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = [
            'categories' => [
                [
                    'category_id' => 1,
                    'name' => 'Beverages',
                    'product_count' => 7,
                ],
                [
                    'category_id' => 2,
                    'name' => 'Snacks',
                    'name_i18n' => ['en' => 'Snacks'],
                    'product_count' => 3,
                ],
            ]
        ];

        $response = CategoryListResponse::createFromArray($data);

        $this->assertCount(2, $response->categories);
        $this->assertInstanceOf(CategoryListEntry::class, $response->categories[0]);
        $this->assertSame(1, $response->categories[0]->category_id);
        $this->assertSame('Beverages', $response->categories[0]->name);
        $this->assertSame(7, $response->categories[0]->product_count);
        $this->assertSame('Snacks', $response->categories[1]->name);
        $this->assertSame('Snacks', $response->categories[1]->name_i18n['en']);
    }
}



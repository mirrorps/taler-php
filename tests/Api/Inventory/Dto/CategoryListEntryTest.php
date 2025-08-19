<?php

namespace Taler\Tests\Api\Inventory\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Inventory\Dto\CategoryListEntry;

class CategoryListEntryTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = [
            'category_id' => 42,
            'name' => 'Beverages',
            'name_i18n' => [
                'en' => 'Beverages',
                'de' => 'Getränke',
            ],
            'product_count' => 7,
        ];

        $entry = CategoryListEntry::createFromArray($data);

        $this->assertSame(42, $entry->category_id);
        $this->assertSame('Beverages', $entry->name);
        $this->assertIsArray($entry->name_i18n);
        $this->assertSame('Getränke', $entry->name_i18n['de']);
        $this->assertSame(7, $entry->product_count);
    }

    public function testCreateFromArrayWithoutI18n(): void
    {
        $data = [
            'category_id' => 1,
            'name' => 'Snacks',
            'product_count' => 3,
        ];

        $entry = CategoryListEntry::createFromArray($data);

        $this->assertSame(1, $entry->category_id);
        $this->assertSame('Snacks', $entry->name);
        $this->assertNull($entry->name_i18n);
        $this->assertSame(3, $entry->product_count);
    }
}



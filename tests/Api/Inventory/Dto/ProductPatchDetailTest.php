<?php

namespace Taler\Tests\Api\Inventory\Dto;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\Location;
use Taler\Api\Dto\Timestamp;
use Taler\Api\Inventory\Dto\ProductPatchDetail;

class ProductPatchDetailTest extends TestCase
{
    public function testValidConstructionMinimal(): void
    {
        $patch = new ProductPatchDetail(description: 'New desc', unit: 'kg', price: 'EUR:1', total_stock: 1);
        $this->assertSame('New desc', $patch->description);
    }

    public function testCreateFromArray(): void
    {
        $patch = ProductPatchDetail::createFromArray([
            'product_name' => 'Name',
            'description_i18n' => ['en' => 'Desc'],
            'categories' => [1, 2],
            'price' => 'EUR:2',
            'total_stock' => 10,
            'total_lost' => 1,
            'address' => ['town' => 'City'],
            'next_restock' => ['t_s' => 'never'],
            'minimum_age' => 18,
            'description' => 'D',
            'unit' => 'u',
        ]);

        $this->assertSame('Name', $patch->product_name);
        $this->assertSame('Desc', $patch->description_i18n['en']);
        $this->assertSame(10, $patch->total_stock);
        $this->assertSame(1, $patch->total_lost);
        $this->assertInstanceOf(Location::class, $patch->address);
        $this->assertInstanceOf(Timestamp::class, $patch->next_restock);
    }

    public function testValidationFailures(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ProductPatchDetail(product_name: '   ', description: 'd', unit: 'u', price: 'p', total_stock: 1);
    }
}



<?php

namespace Taler\Tests\Api\Inventory\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Inventory\Dto\FullInventoryDetailsResponse;
use Taler\Api\Inventory\Dto\MerchantCategory;
use Taler\Api\Inventory\Dto\MerchantPosProductDetail;

class FullInventoryDetailsResponseTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = [
            'products' => [
                [
                    'product_serial' => 1,
                    'product_id' => 'p1',
                    'product_name' => 'Name',
                    'categories' => [1],
                    'description' => 'Desc',
                    'description_i18n' => ['en' => 'Desc'],
                    'unit' => 'kg',
                    'price' => 'EUR:1',
                ]
            ],
            'categories' => [
                ['id' => 1, 'name' => 'Drinks']
            ]
        ];

        $resp = FullInventoryDetailsResponse::createFromArray($data);

        $this->assertCount(1, $resp->products);
        $this->assertCount(1, $resp->categories);
        $this->assertInstanceOf(MerchantPosProductDetail::class, $resp->products[0]);
        $this->assertInstanceOf(MerchantCategory::class, $resp->categories[0]);
        $this->assertSame('p1', $resp->products[0]->product_id);
        $this->assertSame('Drinks', $resp->categories[0]->name);
    }
}



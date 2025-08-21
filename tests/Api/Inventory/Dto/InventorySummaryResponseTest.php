<?php

namespace Taler\Tests\Api\Inventory\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Inventory\Dto\InventoryEntry;
use Taler\Api\Inventory\Dto\InventorySummaryResponse;

class InventorySummaryResponseTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = [
            'products' => [
                ['product_id' => 'p1', 'product_serial' => 1],
                ['product_id' => 'p2', 'product_serial' => 2],
            ]
        ];

        $response = InventorySummaryResponse::createFromArray($data);

        $this->assertCount(2, $response->products);
        $this->assertInstanceOf(InventoryEntry::class, $response->products[0]);
        $this->assertSame('p1', $response->products[0]->product_id);
        $this->assertSame(2, $response->products[1]->product_serial);
    }
}



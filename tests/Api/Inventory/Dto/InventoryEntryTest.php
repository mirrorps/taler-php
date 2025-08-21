<?php

namespace Taler\Tests\Api\Inventory\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Inventory\Dto\InventoryEntry;

class InventoryEntryTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = ['product_id' => 'p1', 'product_serial' => 42];
        $entry = InventoryEntry::createFromArray($data);
        $this->assertSame('p1', $entry->product_id);
        $this->assertSame(42, $entry->product_serial);
    }
}



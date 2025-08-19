<?php

namespace Taler\Tests\Api\Inventory\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Inventory\Dto\CategoryProductSummary;

class CategoryProductSummaryTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = ['product_id' => 'prod-123'];
        $summary = CategoryProductSummary::createFromArray($data);

        $this->assertSame('prod-123', $summary->product_id);
    }
}



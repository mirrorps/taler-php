<?php

namespace Taler\Tests\Api\Inventory\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\Timestamp;
use Taler\Api\Inventory\Dto\OutOfStockResponse;

class OutOfStockResponseTest extends TestCase
{
    public function testCreateFromArrayWithoutRestock(): void
    {
        $data = [
            'product_id' => 'prod-123',
            'requested_quantity' => 10,
            'available_quantity' => 3,
        ];

        $dto = OutOfStockResponse::createFromArray($data);

        $this->assertSame('prod-123', $dto->product_id);
        $this->assertSame(10, $dto->requested_quantity);
        $this->assertSame(3, $dto->available_quantity);
        $this->assertNull($dto->restock_expected);
    }

    public function testCreateFromArrayWithRestock(): void
    {
        $data = [
            'product_id' => 'prod-999',
            'requested_quantity' => 5,
            'available_quantity' => 2,
            'restock_expected' => ['t_s' => 1731000000],
        ];

        $dto = OutOfStockResponse::createFromArray($data);

        $this->assertSame('prod-999', $dto->product_id);
        $this->assertSame(5, $dto->requested_quantity);
        $this->assertSame(2, $dto->available_quantity);
        $this->assertInstanceOf(Timestamp::class, $dto->restock_expected);
        $this->assertSame(1731000000, $dto->restock_expected->t_s);
    }
}



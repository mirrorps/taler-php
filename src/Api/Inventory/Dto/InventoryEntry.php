<?php

namespace Taler\Api\Inventory\Dto;

/**
 * Describes an item in the inventory.
 *
 * Docs shape:
 * {
 *   product_id: string,
 *   product_serial: int
 * }
 *
 * No validation for response DTOs.
 */
class InventoryEntry
{
    public function __construct(
        public readonly string $product_id,
        public readonly int $product_serial,
    ) {}

    /**
     * @param array{product_id: string, product_serial: int} $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            product_id: $data['product_id'],
            product_serial: $data['product_serial']
        );
    }
}



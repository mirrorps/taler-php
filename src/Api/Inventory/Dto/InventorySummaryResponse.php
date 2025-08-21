<?php

namespace Taler\Api\Inventory\Dto;

/**
 * Response DTO for inventory products list.
 *
 * Docs shape:
 * {
 *   products: InventoryEntry[]
 * }
 *
 * No validation for response DTOs.
 */
class InventorySummaryResponse
{
    /**
     * @param array<int, InventoryEntry> $products
     */
    public function __construct(
        public readonly array $products,
    ) {}

    /**
     * @param array{products: array<int, array{product_id: string, product_serial: int}>} $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            products: array_map(
                static fn(array $entry) => InventoryEntry::createFromArray($entry),
                $data['products']
            )
        );
    }
}



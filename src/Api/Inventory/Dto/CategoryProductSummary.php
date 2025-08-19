<?php

namespace Taler\Api\Inventory\Dto;

/**
 * Summary of a product in a category.
 *
 * Docs shape:
 * {
 *   product_id: string
 * }
 *
 * No validation for response DTOs.
 */
class CategoryProductSummary
{
    public function __construct(
        public readonly string $product_id,
    ) {}

    /**
     * @param array{product_id: string} $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            product_id: $data['product_id']
        );
    }
}



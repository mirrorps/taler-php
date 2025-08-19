<?php

namespace Taler\Api\Inventory\Dto;

/**
 * Response DTO for created category.
 *
 * Docs shape:
 * {
 *   category_id: Integer
 * }
 *
 * No validation for response DTOs.
 */
class CategoryCreatedResponse
{
    public function __construct(
        public readonly int $category_id,
    ) {}

    /**
     * @param array{category_id: int} $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            category_id: $data['category_id']
        );
    }
}



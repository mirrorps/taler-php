<?php

namespace Taler\Api\Inventory\Dto;

/**
 * Response DTO for inventory categories list.
 *
 * Docs shape:
 * {
 *   categories: CategoryListEntry[]
 * }
 *
 * No validation for response DTOs.
 */
class CategoryListResponse
{
    /**
     * @param array<int, CategoryListEntry> $categories
     */
    public function __construct(
        public readonly array $categories,
    ) {}

    /**
     * @param array{categories: array<int, array{category_id: int, name: string, name_i18n?: array<string, string>, product_count: int}>} $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            categories: array_map(
                static fn(array $entry) => CategoryListEntry::createFromArray($entry),
                $data['categories']
            )
        );
    }
}



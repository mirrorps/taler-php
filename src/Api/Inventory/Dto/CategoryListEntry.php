<?php

namespace Taler\Api\Inventory\Dto;

/**
 * Describes a single category entry.
 *
 * Docs shape:
 * {
 *   category_id: Integer,
 *   name: string,
 *   name_i18n?: { [lang_tag: string]: string },
 *   product_count: Integer
 * }
 *
 * No validation for response DTOs.
 */
class CategoryListEntry
{
    /**
     * @param int $category_id Unique number for the category
     * @param string $name Name of the category
     * @param array<string, string>|null $name_i18n Translations of the name into various languages
     * @param int $product_count Number of products in this category
     */
    public function __construct(
        public readonly int $category_id,
        public readonly string $name,
        public readonly ?array $name_i18n,
        public readonly int $product_count,
    ) {}

    /**
     * @param array{category_id: int, name: string, name_i18n?: array<string, string>, product_count: int} $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            category_id: $data['category_id'],
            name: $data['name'],
            name_i18n: $data['name_i18n'] ?? null,
            product_count: $data['product_count']
        );
    }
}



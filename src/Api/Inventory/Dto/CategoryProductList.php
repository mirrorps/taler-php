<?php

namespace Taler\Api\Inventory\Dto;

/**
 * Response DTO for a single category's product list.
 *
 * Docs shape:
 * {
 *   name: string,
 *   name_i18n?: { [lang_tag: string]: string },
 *   products: CategoryProductSummary[]
 * }
 *
 * No validation for response DTOs.
 */
class CategoryProductList
{
    /**
     * @param string $name Name of the category
     * @param array<string, string>|null $name_i18n Translations of the name into various languages
     * @param array<int, CategoryProductSummary> $products The products in this category
     */
    public function __construct(
        public readonly string $name,
        public readonly ?array $name_i18n,
        public readonly array $products,
    ) {}

    /**
     * @param array{ name: string, name_i18n?: array<string, string>, products: array<int, array{product_id: string}> } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            name_i18n: $data['name_i18n'] ?? null,
            products: array_map(
                static fn(array $p) => CategoryProductSummary::createFromArray($p),
                $data['products']
            )
        );
    }
}



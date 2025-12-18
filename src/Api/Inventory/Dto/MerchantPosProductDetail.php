<?php

namespace Taler\Api\Inventory\Dto;

use Taler\Api\Dto\Tax;

/**
 * Describes a POS product detail.
 *
 * No validation for response DTOs.
 */
class MerchantPosProductDetail
{
    /**
     * @param int $product_serial Unique numeric ID of the product
     * @param string $product_id Merchant-internal unique identifier for the product
     * @param string $product_name Human-readable product name
     * @param array<int,int> $categories Category IDs the product belongs to
     * @param string $description Human-readable product description
     * @param array<string,string> $description_i18n Localized descriptions
     * @param string $unit Unit in which the product is measured
     * @param string $price The price for one unit (Amount)
     * @param string|null $image Optional base64-encoded product image
     * @param array<int,Tax>|null $taxes List of taxes
     * @param int|null $total_stock Total stock (optional)
     * @param int|null $minimum_age Minimum age (optional)
     */
    public function __construct(
        public readonly int $product_serial,
        public readonly string $product_id,
        public readonly string $product_name,
        public readonly array $categories,
        public readonly string $description,
        public readonly array $description_i18n,
        public readonly string $unit,
        public readonly string $price,
        public readonly ?string $image,
        public readonly ?array $taxes,
        public readonly ?int $total_stock,
        public readonly ?int $minimum_age,
    ) {}

    /**
     * @param array{
     *   product_serial: int,
     *   product_id: string,
     *   product_name: string,
     *   categories: array<int,int>,
     *   description: string,
     *   description_i18n: array<string,string>,
     *   unit: string,
     *   price: string,
     *   image?: string,
     *   taxes?: array<int, array{name: string, tax: string}>,
     *   total_stock?: int,
     *   minimum_age?: int
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            product_serial: $data['product_serial'],
            product_id: $data['product_id'],
            product_name: $data['product_name'],
            categories: $data['categories'],
            description: $data['description'],
            description_i18n: $data['description_i18n'],
            unit: $data['unit'],
            price: $data['price'],
            image: $data['image'] ?? null,
            taxes: isset($data['taxes']) ? array_map(static fn(array $t) => Tax::createFromArray($t), $data['taxes']) : null,
            total_stock: $data['total_stock'] ?? null,
            minimum_age: $data['minimum_age'] ?? null,
        );
    }
}



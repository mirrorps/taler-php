<?php

namespace Taler\Api\Inventory\Dto;

use Taler\Api\Dto\Location;
use Taler\Api\Dto\Tax;
use Taler\Api\Dto\Timestamp;

/**
 * Response DTO describing a product detail in the inventory.
 *
 * No validation for response DTOs.
 */
class ProductDetail
{
    /**
     * @param string $product_name
     * @param string $description
     * @param array<string, string> $description_i18n
     * @param string $unit
     * @param array<int, int> $categories
     * @param string $price
     * @param string $image
     * @param array<int, Tax>|null $taxes
     * @param int $total_stock
     * @param int $total_sold
     * @param int $total_lost
     * @param Location|null $address
     * @param Timestamp|null $next_restock
     * @param int|null $minimum_age
     */
    public function __construct(
        public readonly string $product_name,
        public readonly string $description,
        public readonly array $description_i18n,
        public readonly string $unit,
        public readonly array $categories,
        public readonly string $price,
        public readonly string $image,
        public readonly ?array $taxes,
        public readonly int $total_stock,
        public readonly int $total_sold,
        public readonly int $total_lost,
        public readonly ?Location $address,
        public readonly ?Timestamp $next_restock,
        public readonly ?int $minimum_age,
    ) {}

    /**
     * @param array{
     *   product_name: string,
     *   description: string,
     *   description_i18n: array<string,string>,
     *   unit: string,
     *   categories: array<int,int>,
     *   price: string,
     *   image: string,
     *   taxes?: array<int, array{name: string, tax: string}>,
     *   total_stock: int,
     *   total_sold: int,
     *   total_lost: int,
     *   address?: array<string,mixed>,
     *   next_restock?: array{t_s: int|string},
     *   minimum_age?: int
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            product_name: $data['product_name'],
            description: $data['description'],
            description_i18n: $data['description_i18n'],
            unit: $data['unit'],
            categories: $data['categories'],
            price: $data['price'],
            image: $data['image'],
            taxes: isset($data['taxes']) ? array_map(static fn(array $t) => Tax::fromArray($t), $data['taxes']) : null,
            total_stock: $data['total_stock'],
            total_sold: $data['total_sold'],
            total_lost: $data['total_lost'],
            address: isset($data['address']) ? Location::fromArray($data['address']) : null,
            next_restock: isset($data['next_restock']) ? Timestamp::fromArray($data['next_restock']) : null,
            minimum_age: $data['minimum_age'] ?? null,
        );
    }
}



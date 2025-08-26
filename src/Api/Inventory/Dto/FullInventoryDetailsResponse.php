<?php

namespace Taler\Api\Inventory\Dto;

/**
 * Response DTO for POS configuration with full inventory details.
 *
 * No validation for response DTOs.
 */
class FullInventoryDetailsResponse
{
    /**
     * @param array<int, MerchantPosProductDetail> $products
     * @param array<int, MerchantCategory> $categories
     */
    public function __construct(
        public readonly array $products,
        public readonly array $categories,
    ) {}

    /**
     * @param array{
     *   products: array<int, array{
     *     product_serial: int,
     *     product_id: string,
     *     product_name: string,
     *     categories: array<int,int>,
     *     description: string,
     *     description_i18n: array<string,string>,
     *     unit: string,
     *     price: string,
     *     image?: string,
     *     taxes?: array<int, array{name: string, tax: string}>,
     *     total_stock?: int,
     *     minimum_age?: int
     *   }>,
     *   categories: array<int, array{id: int, name: string, name_i18n?: array<string,string>}>}
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            products: array_map(
                static fn(array $p) => MerchantPosProductDetail::createFromArray($p),
                $data['products']
            ),
            categories: array_map(
                static fn(array $c) => MerchantCategory::createFromArray($c),
                $data['categories']
            )
        );
    }
}



<?php

namespace Taler\Api\Inventory\Dto;

use Taler\Api\Dto\Timestamp;

/**
 * Response DTO for out-of-stock item information.
 *
 * Docs shape:
 * interface OutOfStockResponse {
 *   product_id: string;
 *   requested_quantity: Integer;
 *   available_quantity: Integer;
 *   restock_expected?: Timestamp;
 * }
 *
 * No validation for response DTOs.
 */
class OutOfStockResponse
{
    public function __construct(
        public readonly string $product_id,
        public readonly int $requested_quantity,
        public readonly int $available_quantity,
        public readonly ?Timestamp $restock_expected,
    ) {}

    /**
     * @param array{
     *   product_id: string,
     *   requested_quantity: int,
     *   available_quantity: int,
     *   restock_expected?: array{t_s: int|string}
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            product_id: $data['product_id'],
            requested_quantity: $data['requested_quantity'],
            available_quantity: $data['available_quantity'],
            restock_expected: isset($data['restock_expected']) ? Timestamp::createFromArray($data['restock_expected']) : null,
        );
    }
}



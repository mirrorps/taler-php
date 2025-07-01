<?php

namespace Taler\Api\Inventory\Dto;

use InvalidArgumentException;

/**
 * MinimalInventoryProduct DTO
 *
 * @phpstan-type MinimalInventoryProductArray array{
 *   product_id: string,
 *   quantity: int
 * }
 */
class MinimalInventoryProduct
{
    /**
     * @param string $product_id Which product is requested (here mandatory!)
     * @param int $quantity How many units of the product are requested
     * @param bool $validate Whether to validate the properties
     * @throws InvalidArgumentException When validation fails
     */
    public function __construct(
        private string $product_id,
        private int $quantity,
        private bool $validate = true,
    ) {
        if($this->validate) {
            $this->validate();
        }
    }

    /**
     * Get the product ID
     */
    public function getProductId(): string
    {
        return $this->product_id;
    }

    /**
     * Get the quantity
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * Validates all properties
     *
     * @throws InvalidArgumentException
     */
    private function validate(): void
    {
        if (empty(trim($this->product_id))) {
            throw new InvalidArgumentException('Product ID must be a non-empty string');
        }

        if ($this->quantity <= 0) {
            throw new InvalidArgumentException('Quantity must be greater than zero');
        }
    }

    /**
     * Converts the DTO to an array suitable for API requests
     *
     * @return MinimalInventoryProductArray
     */
    public function toArray(): array
    {
        return [
            'product_id' => $this->product_id,
            'quantity' => $this->quantity,
        ];
    }
} 
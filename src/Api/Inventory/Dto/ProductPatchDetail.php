<?php

namespace Taler\Api\Inventory\Dto;

use Taler\Api\Dto\Location;
use Taler\Api\Dto\Tax;
use Taler\Api\Dto\Timestamp;

/**
 * Request DTO for updating a product in the inventory.
 *
 * All fields are optional. Fields not supplied are preserved.
 */
class ProductPatchDetail implements \JsonSerializable
{
    /**
     * @param string|null $product_name Human-readable product name (v20+)
     * @param string $description Human-readable product description
     * @param array<string, string>|null $description_i18n Map of localized descriptions
     * @param string $unit Unit in which the product is measured
     * @param array<int, int>|null $categories Category IDs
     * @param string $price The price for one unit (Amount)
     * @param string|null $image Optional base64-encoded product image
     * @param array<int, Tax>|null $taxes Taxes paid for one unit
     * @param int $total_stock Total units in stock (cumulative)
     * @param int|null $total_lost Total units lost (cumulative)
     * @param Location|null $address Where the product is in stock
     * @param Timestamp|null $next_restock Expected next restock (use "never" for none)
     * @param int|null $minimum_age Minimum buyer age in years
     * @param bool $validate Whether to validate input
     */
    public function __construct(
        public readonly string $description,
        public readonly string $unit,
        public readonly string $price,
        public readonly int $total_stock,
        public readonly ?string $product_name = null,
        public readonly ?array $description_i18n = null,
        public readonly ?array $categories = null,
        public readonly ?string $image = null,
        public readonly ?array $taxes = null,
        public readonly ?int $total_lost = null,
        public readonly ?Location $address = null,
        public readonly ?Timestamp $next_restock = null,
        public readonly ?int $minimum_age = null,
        bool $validate = true
    ) {
        if ($validate) {
            $this->validate();
        }
    }

    /**
     * @param array{
     *   product_name?: string,
     *   description: string,
     *   description_i18n?: array<string,string>,
     *   unit: string,
     *   categories?: array<int,int>,
     *   price: string,
     *   image?: string,
     *   taxes?: array<int, array{name: string, tax: string}>,
     *   total_stock: int,
     *   total_lost?: int,
     *   address?: array<string, mixed>,
     *   next_restock?: array{t_s: int|string},
     *   minimum_age?: int
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            description: $data['description'],
            unit: $data['unit'],
            price: $data['price'],
            total_stock: $data['total_stock'],
            product_name: $data['product_name'] ?? null,
            description_i18n: $data['description_i18n'] ?? null,
            categories: $data['categories'] ?? null,
            image: $data['image'] ?? null,
            taxes: isset($data['taxes']) ? array_map(static fn(array $t) => Tax::createFromArray($t), $data['taxes']) : null,
            total_lost: $data['total_lost'] ?? null,
            address: isset($data['address']) ? Location::createFromArray($data['address']) : null,
            next_restock: isset($data['next_restock']) ? Timestamp::createFromArray($data['next_restock']) : null,
            minimum_age: $data['minimum_age'] ?? null,
        );
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function validate(): void
    {
        if ($this->product_name !== null && trim($this->product_name) === '') {
            throw new \InvalidArgumentException('product_name, when provided, must not be empty');
        }
        if (trim($this->description) === '') {
            throw new \InvalidArgumentException('description, when provided, must not be empty');
        }
        if (trim($this->unit) === '') {
            throw new \InvalidArgumentException('unit, when provided, must not be empty');
        }
        if (trim($this->price) === '') {
            throw new \InvalidArgumentException('price, when provided, must not be empty');
        }
        if ($this->description_i18n !== null) {
            foreach ($this->description_i18n as $lang => $value) {
                if ($lang === '' || trim($lang) === '') {
                    throw new \InvalidArgumentException('description_i18n language tag keys must not be empty');
                }
                if (trim($value) === '') {
                    throw new \InvalidArgumentException('description_i18n values must be non-empty strings');
                }
            }
        }
        // categories item type validated by PHPStan
        if ($this->minimum_age !== null && $this->minimum_age < 0) {
            throw new \InvalidArgumentException('minimum_age must be non-negative');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'product_name' => $this->product_name,
            'description' => $this->description,
            'description_i18n' => $this->description_i18n,
            'unit' => $this->unit,
            'categories' => $this->categories,
            'price' => $this->price,
            'image' => $this->image,
            'taxes' => $this->taxes,
            'total_stock' => $this->total_stock,
            'total_lost' => $this->total_lost,
            'address' => $this->address,
            'next_restock' => $this->next_restock,
            'minimum_age' => $this->minimum_age,
        ];
    }
}



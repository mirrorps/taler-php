<?php

namespace Taler\Api\Inventory\Dto;

use Taler\Api\Dto\Location;
use Taler\Api\Dto\Tax;
use Taler\Api\Dto\Timestamp;

/**
 * Request DTO for adding a product to the inventory.
 *
 * Shape:
 * {
 *   product_id: string,
 *   product_name?: string,
 *   description: string,
 *   description_i18n?: { [lang_tag: string]: string },
 *   categories?: Integer[],
 *   unit: string,
 *   price: string, // Amount
 *   image?: string, // base64 data URL
 *   taxes?: Tax[],
 *   total_stock: int,
 *   address?: Location,
 *   next_restock?: Timestamp,
 *   minimum_age?: int
 * }
 */
class ProductAddDetail implements \JsonSerializable
{
    /**
     * @param string $product_id Product ID to use
     * @param string $description Human-readable product description
     * @param string $unit Unit in which the product is measured
     * @param string $price The price for one unit of the product (Amount)
     * @param int $total_stock Number of units in stock (use -1 for infinite)
     * @param string|null $product_name Human-readable product name
     * @param array<string, string>|null $description_i18n Map of localized descriptions
     * @param array<int, int>|null $categories Category IDs
     * @param string|null $image Optional base64-encoded product image
     * @param array<int, Tax>|null $taxes A list of taxes paid for one unit
     * @param Location|null $address Where the product is in stock
     * @param Timestamp|null $next_restock When the next restocking is expected
     * @param int|null $minimum_age Minimum buyer age in years
     * @param bool $validate Whether to validate input
     */
    public function __construct(
        public readonly string $product_id,
        public readonly string $description,
        public readonly string $unit,
        public readonly string $price,
        public readonly int $total_stock,
        public readonly ?string $product_name = null,
        public readonly ?array $description_i18n = null,
        public readonly ?array $categories = null,
        public readonly ?string $image = null,
        public readonly ?array $taxes = null,
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
     *   product_id: string,
     *   product_name?: string,
     *   description: string,
     *   description_i18n?: array<string,string>,
     *   categories?: array<int,int>,
     *   unit: string,
     *   price: string,
     *   image?: string,
     *   taxes?: array<int, array{name: string, tax: string}>,
     *   total_stock: int,
     *   address?: array<string, mixed>,
     *   next_restock?: array{t_s: int|string},
     *   minimum_age?: int
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            product_id: $data['product_id'],
            product_name: $data['product_name'] ?? null,
            description: $data['description'],
            description_i18n: $data['description_i18n'] ?? null,
            categories: $data['categories'] ?? null,
            unit: $data['unit'],
            price: $data['price'],
            image: $data['image'] ?? null,
            taxes: isset($data['taxes']) ? array_map(static fn(array $t) => Tax::fromArray($t), $data['taxes']) : null,
            total_stock: $data['total_stock'],
            address: isset($data['address']) ? Location::fromArray($data['address']) : null,
            next_restock: isset($data['next_restock']) ? Timestamp::fromArray($data['next_restock']) : null,
            minimum_age: $data['minimum_age'] ?? null,
        );
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function validate(): void
    {
        if ($this->product_id === '' || trim($this->product_id) === '') {
            throw new \InvalidArgumentException('product_id must not be empty');
        }
        if ($this->description === '' || trim($this->description) === '') {
            throw new \InvalidArgumentException('description must not be empty');
        }
        if ($this->unit === '' || trim($this->unit) === '') {
            throw new \InvalidArgumentException('unit must not be empty');
        }
        if ($this->price === '' || trim($this->price) === '') {
            throw new \InvalidArgumentException('price must not be empty');
        }
        // total_stock is typed as int
        if ($this->product_name !== null && trim($this->product_name) === '') {
            throw new \InvalidArgumentException('product_name, when provided, must not be empty');
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
            'product_id' => $this->product_id,
            'product_name' => $this->product_name,
            'description' => $this->description,
            'description_i18n' => $this->description_i18n,
            'categories' => $this->categories,
            'unit' => $this->unit,
            'price' => $this->price,
            'image' => $this->image,
            'taxes' => $this->taxes,
            'total_stock' => $this->total_stock,
            'address' => $this->address,
            'next_restock' => $this->next_restock,
            'minimum_age' => $this->minimum_age,
        ];
    }
}



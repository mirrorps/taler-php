<?php

namespace Taler\Api\Dto;

/**
 * DTO for Product data
 */
class Product
{
    /**
     * @param string|null $product_id Merchant-internal identifier for the product
     * @param string $description Human-readable product description
     * @param array<string, string>|null $description_i18n Map from IETF BCP 47 language tags to localized descriptions
     * @param int|null $quantity The number of units of the product to deliver to the customer
     * @param string|null $unit Unit in which the product is measured (liters, kilograms, packages, etc.)
     * @param string|null $price The price of the product; this is the total price for quantity times unit of this product
     * @param string|null $image An optional base64-encoded product image
     * @param array<int, Tax>|null $taxes A list of taxes paid by the merchant for this product
     * @param Timestamp|null $delivery_date Time indicating when this product should be delivered
     */
    public function __construct(
        public readonly string $description,
        public readonly ?string $product_id = null,
        public readonly ?array $description_i18n = null,
        public readonly ?int $quantity = null,
        public readonly ?string $unit = null,
        public readonly ?string $price = null,
        public readonly ?string $image = null,
        public readonly ?array $taxes = null,
        public readonly ?Timestamp $delivery_date = null,
    ) {
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param array{
     *     product_id?: string|null,
     *     description?: string,
     *     description_i18n?: array<string, string>|null,
     *     quantity?: int|null,
     *     unit?: string|null,
     *     price?: string|null,
     *     image?: string|null,
     *     taxes?: array<int, array{name: string, tax: string}>|null,
     *     delivery_date?: array{t_s: int|string}|null
     * } $data
     * @return self
     * @throws \InvalidArgumentException When required data is missing or invalid
     */
    public static function fromArray(array $data): self
    {
        $taxes = null;
        if (isset($data['taxes'])) {
            $taxes = array_map(
                fn (array $key) => Tax::fromArray($key),
                $data['taxes']
            );
        }

        $delivery_date = null;
        if (isset($data['delivery_date'])) {
            $delivery_date = Timestamp::fromArray($data['delivery_date']);
        }

        return new self(
            description: $data['description'] ?? null,
            product_id: $data['product_id'] ?? null,
            description_i18n: $data['description_i18n'] ?? null,
            quantity: $data['quantity'] ?? null,
            unit: $data['unit'] ?? null,
            price: $data['price'] ?? null,
            image: $data['image'] ?? null,
            taxes: $taxes,
            delivery_date: $delivery_date
        );
    }
} 
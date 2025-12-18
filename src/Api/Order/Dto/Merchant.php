<?php

namespace Taler\Api\Order\Dto;

use Taler\Api\Dto\Location;

class Merchant
{
    /**
     * @param string $name The merchant's legal name of business
     * @param string|null $email Optional email address
     * @param string|null $website Optional website URL
     * @param string|null $logo Optional base64-encoded product image
     * @param Location|null $address Optional business address of the merchant
     * @param Location|null $jurisdiction Optional jurisdiction for disputes
     */
    public function __construct(
        public readonly string $name,
        public readonly ?string $email = null,
        public readonly ?string $website = null,
        public readonly ?string $logo = null,
        public readonly ?Location $address = null,
        public readonly ?Location $jurisdiction = null
    ) {}

    /**
     * @param array{
     *     name: string,
     *     email?: string|null,
     *     website?: string|null,
     *     logo?: string|null,
     *     address?: array{country?: string|null, town?: string|null, state?: string|null, region?: string|null, province?: string|null, street?: string|null}|null,
     *     jurisdiction?: array{country?: string|null, town?: string|null, state?: string|null, region?: string|null, province?: string|null, street?: string|null}|null
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            $data['name'],
            $data['email'] ?? null,
            $data['website'] ?? null,
            $data['logo'] ?? null,
            isset($data['address']) ? Location::createFromArray($data['address']) : null,
            isset($data['jurisdiction']) ? Location::createFromArray($data['jurisdiction']) : null
        );
    }
} 
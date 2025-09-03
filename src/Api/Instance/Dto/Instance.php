<?php

namespace Taler\Api\Instance\Dto;

/**
 * DTO for Instance summary information
 *
 * Note: Response DTOs do not include validation.
 */
class Instance
{
    /**
     * @param string $name Merchant name corresponding to this instance
     * @param string $id Merchant instance this response is about ($INSTANCE)
     * @param string $merchant_pub Public key of the merchant/instance (EddsaPublicKey)
     * @param array<int, string> $payment_targets List of supported payment targets
     * @param bool $deleted Has this instance been deleted (but not purged)?
     * @param string|null $website Merchant public website
     * @param string|null $logo Merchant logo (ImageDataUrl)
     */
    public function __construct(
        public readonly string $name,
        public readonly string $id,
        public readonly string $merchant_pub,
        public readonly array $payment_targets,
        public readonly bool $deleted,
        public readonly ?string $website = null,
        public readonly ?string $logo = null,
    ) {
    }

    /**
     * @param array{
     *   name: string,
     *   id: string,
     *   merchant_pub: string,
     *   payment_targets: array<int, string>,
     *   deleted: bool,
     *   website?: string|null,
     *   logo?: string|null
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            id: $data['id'],
            merchant_pub: $data['merchant_pub'],
            payment_targets: $data['payment_targets'],
            deleted: $data['deleted'],
            website: $data['website'] ?? null,
            logo: $data['logo'] ?? null
        );
    }
}



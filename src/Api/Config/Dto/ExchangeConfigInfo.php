<?php

namespace Taler\Api\Config\Dto;

/**
 * Exchange configuration information for the merchant config response.
 */
class ExchangeConfigInfo
{
    public function __construct(
        public readonly string $base_url,
        public readonly string $currency,
        public readonly string $master_pub,
    ) {}

    /**
     * @param array{base_url: string, currency: string, master_pub: string} $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            base_url: $data['base_url'],
            currency: $data['currency'],
            master_pub: $data['master_pub']
        );
    }
}



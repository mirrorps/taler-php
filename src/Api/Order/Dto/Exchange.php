<?php

namespace Taler\Api\Order\Dto;

class Exchange
{
    /**
     * @param string $url The exchange's base URL
     * @param int $priority How much would the merchant like to use this exchange
     * @param string $master_pub Master public key of the exchange (EddsaPublicKey)
     * @param string|null $max_contribution Maximum amount that the merchant could be paid (optional)
     */
    public function __construct(
        public readonly string $url,
        public readonly int $priority,
        public readonly string $master_pub,
        public readonly ?string $max_contribution = null
    ) {}

    /**
     * @param array{
     *     url: string,
     *     priority: int,
     *     master_pub: string,
     *     max_contribution?: string|null
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            $data['url'],
            $data['priority'],
            $data['master_pub'],
            $data['max_contribution'] ?? null
        );
    }
} 
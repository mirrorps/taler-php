<?php

namespace Taler\Api\ContractTerms\Dto;

/**
 * DTO for contract terms version 0 data
 * 
 * @see https://docs.taler.net/core/api-common.html
 */
class ContractTermsV0
{
    /**
     * @param string $amount Total price for the transaction. The exchange will subtract deposit fees from that amount before transferring it to the merchant.
     * @param string $max_fee Maximum total deposit fee accepted by the merchant for this contract. Overrides defaults of the merchant instance.
     * @param int|null $version Defaults to version 0.
     */
    public function __construct(
        public readonly string $amount,
        public readonly string $max_fee,
        public readonly ?int $version = 0,
    ) {
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param array{
     *     amount: string,
     *     max_fee: string,
     *     version?: int|null
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            amount: $data['amount'],
            max_fee: $data['max_fee'],
            version: $data['version'] ?? 0
        );
    }
} 
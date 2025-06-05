<?php

namespace Taler\Api\Exchange\Dto;

/**
 * DTO for the track transfer detail from the exchange API
 * 
 * @see https://docs.taler.net/core/api-exchange.html#get--transfers-$WTID
 */
class TrackTransferDetail
{
    /**
     * @param string $h_contract_terms SHA-512 hash of the contact of the merchant with the customer
     * @param string $coin_pub Coin's public key, both ECDHE and EdDSA
     * @param string $deposit_value The total amount the original deposit was worth, including fees and after applicable refunds
     * @param string $deposit_fee Applicable fees for the deposit, possibly reduced or waived due to refunds
     * @param string $refund_total Refunds that were applied to the value of this coin (Optional)
     */
    public function __construct(
        public readonly string $h_contract_terms,
        public readonly string $coin_pub,
        public readonly string $deposit_value,
        public readonly string $deposit_fee,
        public readonly ?string $refund_total = null,
    ) {
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param array{
     *     h_contract_terms: string,
     *     coin_pub: string,
     *     deposit_value: string,
     *     deposit_fee: string,
     *     refund_total?: string|null
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            h_contract_terms: $data['h_contract_terms'],
            coin_pub: $data['coin_pub'],
            deposit_value: $data['deposit_value'],
            deposit_fee: $data['deposit_fee'],
            refund_total: $data['refund_total'] ?? null
        );
    }
} 
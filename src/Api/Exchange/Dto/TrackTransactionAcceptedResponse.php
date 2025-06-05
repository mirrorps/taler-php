<?php

namespace Taler\Api\Exchange\Dto;

/**
 * DTO for the track transaction accepted response from the exchange API
 * 
 * @see https://docs.taler.net/core/api-exchange.html#get--deposits-$H_WIRE-$MERCHANT_PUB-$H_CONTRACT_TERMS-$COIN_PUB
 */
class TrackTransactionAcceptedResponse
{
    /**
     * @param int|null $requirement_row Legitimization row. Largely useless, except not present if the deposit has not yet been aggregated to the point that a KYC requirement has been evaluated.
     * @param bool $kyc_ok True if the KYC check for the merchant has been satisfied. False does not mean that KYC is strictly needed, unless also a legitimization_uuid is provided.
     * @param string $execution_time Time by which the exchange currently thinks the deposit will be executed. Actual execution may be later if the KYC check is not satisfied by then.
     * @param string|null $account_pub Public key associated with the account. Only given if the merchant did a KYC auth wire transfer. Absent if no public key is currently associated with the account.
     */
    public function __construct(
        public readonly ?int $requirement_row,
        public readonly bool $kyc_ok,
        public readonly string $execution_time,
        public readonly ?string $account_pub,
    ) {
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param array{
     *     requirement_row?: int|null,
     *     kyc_ok: bool,
     *     execution_time: string,
     *     account_pub?: string|null
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            requirement_row: $data['requirement_row'] ?? null,
            kyc_ok: $data['kyc_ok'],
            execution_time: $data['execution_time'],
            account_pub: $data['account_pub'] ?? null
        );
    }
} 
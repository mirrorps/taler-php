<?php

namespace Taler\Api\Exchange\Dto;

use Taler\Api\Dto\Timestamp;

/**
 * DTO for the track transaction response from the exchange API
 * 
 * @see https://docs.taler.net/core/api-exchange.html#get--deposits-$H_WIRE-$MERCHANT_PUB-$H_CONTRACT_TERMS-$COIN_PUB
 */
class TrackTransactionResponse
{
    /**
     * @param string $wtid Raw wire transfer identifier of the deposit (Base32)
     * @param Timestamp $execution_time When was the wire transfer given to the bank (Timestamp)
     * @param string $coin_contribution The contribution of this coin to the total (without fees)
     * @param string $exchange_sig Binary-only Signature with purpose TALER_SIGNATURE_EXCHANGE_CONFIRM_WIRE over a TALER_ConfirmWirePS
     * @param string $exchange_pub Public EdDSA key of the exchange used to generate the signature
     */
    public function __construct(
        public readonly string $wtid,
        public readonly Timestamp $execution_time,
        public readonly string $coin_contribution,
        public readonly string $exchange_sig,
        public readonly string $exchange_pub,
    ) {
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param array{
     *     wtid: string,
     *     execution_time: array{t_s: int|string},
     *     coin_contribution: string,
     *     exchange_sig: string,
     *     exchange_pub: string
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            wtid: $data['wtid'],
            execution_time: Timestamp::fromArray($data['execution_time']),
            coin_contribution: $data['coin_contribution'],
            exchange_sig: $data['exchange_sig'],
            exchange_pub: $data['exchange_pub']
        );
    }
} 
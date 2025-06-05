<?php

namespace Taler\Api\Exchange\Dto;

/**
 * DTO for the track transfer response from the exchange API
 * 
 * @see https://docs.taler.net/core/api-exchange.html#get--transfers-$WTID
 */
class TrackTransferResponse
{
    /**
     * @param string $total Actual amount of the wire transfer, excluding the wire fee
     * @param string $wire_fee Applicable wire fee that was charged
     * @param string $merchant_pub Public key of the merchant (identical for all deposits)
     * @param string $h_payto Hash of the payto:// account URI (identical for all deposits)
     * @param string $execution_time Time of the execution of the wire transfer by the exchange
     * @param array<TrackTransferDetail> $deposits Details about the deposits
     * @param string $exchange_sig Signature from the exchange made with purpose TALER_SIGNATURE_EXCHANGE_CONFIRM_WIRE_DEPOSIT over a TALER_WireDepositDataPS
     * @param string $exchange_pub Public EdDSA key of the exchange that was used to generate the signature
     */
    public function __construct(
        public readonly string $total,
        public readonly string $wire_fee,
        public readonly string $merchant_pub,
        public readonly string $h_payto,
        public readonly string $execution_time,
        public readonly array $deposits,
        public readonly string $exchange_sig,
        public readonly string $exchange_pub,
    ) {
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param array{
     *     total: string,
     *     wire_fee: string,
     *     merchant_pub: string,
     *     h_payto: string,
     *     execution_time: string,
     *     deposits: array<int, array{
     *         h_contract_terms: string,
     *         coin_pub: string,
     *         deposit_value: string,
     *         deposit_fee: string,
     *         refund_total?: string|null
     *     }>,
     *     exchange_sig: string,
     *     exchange_pub: string
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            total: $data['total'],
            wire_fee: $data['wire_fee'],
            merchant_pub: $data['merchant_pub'],
            h_payto: $data['h_payto'],
            execution_time: $data['execution_time'],
            deposits: array_map(
                fn(array $detail) => TrackTransferDetail::fromArray($detail),
                $data['deposits']
            ),
            exchange_sig: $data['exchange_sig'],
            exchange_pub: $data['exchange_pub']
        );
    }
} 
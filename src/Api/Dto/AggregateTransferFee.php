<?php

namespace Taler\Api\Dto;

/**
 * DTO for aggregate transfer fees in the Taler exchange
 *
 * @see https://docs.taler.net/core/api-exchange.html
 */
class AggregateTransferFee
{
    /**
     * @param string $wire_fee Per transfer wire transfer fee
     * @param string $closing_fee Per transfer closing fee
     * @param Timestamp $start_date What date (inclusive) does this fee go into effect
     * @param Timestamp $end_date What date (exclusive) does this fee stop going into effect
     * @param string $sig Signature of TALER_MasterWireFeePS with purpose TALER_SIGNATURE_MASTER_WIRE_FEES
     */
    public function __construct(
        public readonly string $wire_fee,
        public readonly string $closing_fee,
        public readonly Timestamp $start_date,
        public readonly Timestamp $end_date,
        public readonly string $sig,
    ) {
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param array{
     *     wire_fee: string,
     *     closing_fee: string,
     *     start_date: array{t_s: int|string},
     *     end_date: array{t_s: int|string},
     *     sig: string
     * } $data
     * @throws \InvalidArgumentException if required fields are missing or invalid
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            wire_fee: $data['wire_fee'],
            closing_fee: $data['closing_fee'],
            start_date: Timestamp::createFromArray($data['start_date']),
            end_date: Timestamp::createFromArray($data['end_date']),
            sig: $data['sig']
        );
    }
} 
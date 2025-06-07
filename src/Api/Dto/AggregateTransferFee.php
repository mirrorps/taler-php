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
     * @param string $start_date What date (inclusive) does this fee go into effect
     * @param string $end_date What date (exclusive) does this fee stop going into effect
     * @param string $sig Signature of TALER_MasterWireFeePS with purpose TALER_SIGNATURE_MASTER_WIRE_FEES
     */
    public function __construct(
        public readonly string $wire_fee,
        public readonly string $closing_fee,
        public readonly string $start_date,
        public readonly string $end_date,
        public readonly string $sig,
    ) {
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param array{
     *     wire_fee?: string,
     *     closing_fee?: string,
     *     start_date?: string,
     *     end_date?: string,
     *     sig?: string
     * } $data
     * @throws \InvalidArgumentException if required fields are missing or invalid
     */
    public static function fromArray(array $data): self
    {
        if (!isset($data['wire_fee'])) {
            throw new \InvalidArgumentException('Missing wire_fee field');
        }

        if (!isset($data['closing_fee'])) {
            throw new \InvalidArgumentException('Missing closing_fee field');
        }

        if (!isset($data['start_date'])) {
            throw new \InvalidArgumentException('Missing start_date field');
        }

        if (!isset($data['end_date'])) {
            throw new \InvalidArgumentException('Missing end_date field');
        }

        if (!isset($data['sig'])) {
            throw new \InvalidArgumentException('Missing sig field');
        }

        return new self(
            wire_fee: $data['wire_fee'],
            closing_fee: $data['closing_fee'],
            start_date: $data['start_date'],
            end_date: $data['end_date'],
            sig: $data['sig']
        );
    }
} 
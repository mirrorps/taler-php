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
     * @param Timestamp|string $start_date What date (inclusive) does this fee go into effect
     * @param Timestamp|string $end_date What date (exclusive) does this fee stop going into effect
     * @param string $sig Signature of TALER_MasterWireFeePS with purpose TALER_SIGNATURE_MASTER_WIRE_FEES
     */
    public function __construct(
        public readonly string $wire_fee,
        public readonly string $closing_fee,
        public readonly Timestamp|string $start_date,
        public readonly Timestamp|string $end_date,
        public readonly string $sig,
    ) {
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param array{
     *     wire_fee?: string,
     *     closing_fee?: string,
     *     start_date?: array{t_s: int|string}|string,
     *     end_date?: array{t_s: int|string}|string,
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

        // Handle flexible timestamp format - could be string or RelativeTime
        $startDate = self::parseTimestamp($data['start_date']);
        $endDate = self::parseTimestamp($data['end_date']);

        return new self(
            wire_fee: $data['wire_fee'],
            closing_fee: $data['closing_fee'],
            start_date: $startDate,
            end_date: $endDate,
            sig: $data['sig']
        );
    }

    /**
     * Parse timestamp data that could be a string or Timestamp array
     *
     * @param mixed $timestampData
     * @return Timestamp|string
     * @throws \InvalidArgumentException
     */
    private static function parseTimestamp(mixed $timestampData): Timestamp|string
    {
        if (is_string($timestampData)) {
            // Return string timestamps as-is (ISO format)
            return $timestampData;
        }

        if (is_array($timestampData)) {
            if (isset($timestampData['t_s'])) {
                // Parse as Timestamp if it has t_s key
                return Timestamp::fromArray($timestampData);
            }
        }

        throw new \InvalidArgumentException('Invalid timestamp format: ' . var_export($timestampData, true));
    }
} 
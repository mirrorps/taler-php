<?php

namespace Taler\Api\Instance\Dto;

use Taler\Api\Dto\Timestamp;

/**
 * Statistics kept for a particular sliding interval where values are counters
 */
class MerchantStatisticCounterByInterval
{
    /**
     * @param Timestamp $start_time Start time of the interval (ends at response time)
     * @param int $cumulative_counter Sum of all counters within this timeframe
     */
    public function __construct(
        public readonly Timestamp $start_time,
        public readonly int $cumulative_counter,
    ) {
    }

    /**
     * @param array{
     *   start_time: array{t_s: int|string},
     *   cumulative_counter: int
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            start_time: Timestamp::fromArray($data['start_time']),
            cumulative_counter: $data['cumulative_counter']
        );
    }
}




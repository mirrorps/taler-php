<?php

namespace Taler\Api\Instance\Dto;

use Taler\Api\Dto\Timestamp;

/**
 * Statistics kept for a particular fixed time window (bucket) where values are counters
 */
class MerchantStatisticCounterByBucket
{
    /**
     * @param Timestamp $start_time Start time of the bucket (inclusive)
     * @param Timestamp $end_time End time of the bucket (exclusive)
     * @param string $range Range of the bucket (StatisticBucketRange)
     * @param int $cumulative_counter Sum of all counters within this timeframe
     */
    public function __construct(
        public readonly Timestamp $start_time,
        public readonly Timestamp $end_time,
        public readonly string $range,
        public readonly int $cumulative_counter,
    ) {
    }

    /**
     * @param array{
     *   start_time: array{t_s: int|string},
     *   end_time: array{t_s: int|string},
     *   range: string,
     *   cumulative_counter: int
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            start_time: Timestamp::createFromArray($data['start_time']),
            end_time: Timestamp::createFromArray($data['end_time']),
            range: $data['range'],
            cumulative_counter: $data['cumulative_counter']
        );
    }
}




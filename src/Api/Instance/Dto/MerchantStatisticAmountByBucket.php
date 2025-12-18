<?php

namespace Taler\Api\Instance\Dto;

use Taler\Api\Dto\Timestamp;

/**
 * Statistics kept for a particular fixed time window (bucket)
 */
class MerchantStatisticAmountByBucket
{
    /**
     * @param Timestamp $start_time Start time of the bucket (inclusive)
     * @param Timestamp $end_time End time of the bucket (exclusive)
     * @param string $range Range of the bucket (StatisticBucketRange)
     * @param array<int,string> $cumulative_amounts Sum of all amounts (Amount strings)
     */
    public function __construct(
        public readonly Timestamp $start_time,
        public readonly Timestamp $end_time,
        public readonly string $range,
        public readonly array $cumulative_amounts,
    ) {
    }

    /**
     * @param array{
     *   start_time: array{t_s: int|string},
     *   end_time: array{t_s: int|string},
     *   range: string,
     *   cumulative_amounts: array<int,string>
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            start_time: Timestamp::createFromArray($data['start_time']),
            end_time: Timestamp::createFromArray($data['end_time']),
            range: $data['range'],
            cumulative_amounts: $data['cumulative_amounts']
        );
    }
}



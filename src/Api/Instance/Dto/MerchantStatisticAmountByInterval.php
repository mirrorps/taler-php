<?php

namespace Taler\Api\Instance\Dto;

use Taler\Api\Dto\Timestamp;

/**
 * Statistics kept for a particular sliding interval
 */
class MerchantStatisticAmountByInterval
{
    /**
     * @param Timestamp $start_time Start time of the interval (ends at response time)
     * @param array<int,string> $cumulative_amounts Sum of all amounts (Amount strings)
     */
    public function __construct(
        public readonly Timestamp $start_time,
        public readonly array $cumulative_amounts,
    ) {
    }

    /**
     * @param array{
     *   start_time: array{t_s: int|string},
     *   cumulative_amounts: array<int,string>
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            start_time: Timestamp::createFromArray($data['start_time']),
            cumulative_amounts: $data['cumulative_amounts']
        );
    }
}



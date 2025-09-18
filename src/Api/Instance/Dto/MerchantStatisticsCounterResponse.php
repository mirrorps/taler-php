<?php

namespace Taler\Api\Instance\Dto;

/**
 * Merchant statistics where values are counters
 */
class MerchantStatisticsCounterResponse
{
    /**
     * @param array<int,MerchantStatisticCounterByBucket> $buckets Statistics kept for a particular fixed time window
     * @param array<int,MerchantStatisticCounterByInterval> $intervals Statistics kept for a particular sliding interval
     * @param string|null $buckets_description Human-readable bucket statistic description
     * @param string|null $intervals_description Human-readable interval statistic description
     */
    public function __construct(
        public readonly array $buckets,
        public readonly array $intervals,
        public readonly ?string $buckets_description = null,
        public readonly ?string $intervals_description = null,
    ) {
    }

    /**
     * @param array{
     *   buckets: array<int, array{
     *     start_time: array{t_s: int|string},
     *     end_time: array{t_s: int|string},
     *     range: string,
     *     cumulative_counter: int
     *   }>,
     *   intervals: array<int, array{
     *     start_time: array{t_s: int|string},
     *     cumulative_counter: int
     *   }>,
     *   buckets_description?: string|null,
     *   intervals_description?: string|null
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            buckets: array_map(
                fn(array $item) => MerchantStatisticCounterByBucket::createFromArray($item),
                $data['buckets']
            ),
            intervals: array_map(
                fn(array $item) => MerchantStatisticCounterByInterval::createFromArray($item),
                $data['intervals']
            ),
            buckets_description: $data['buckets_description'] ?? null,
            intervals_description: $data['intervals_description'] ?? null
        );
    }
}




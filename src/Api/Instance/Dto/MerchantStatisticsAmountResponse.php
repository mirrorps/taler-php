<?php

namespace Taler\Api\Instance\Dto;

/**
 * Merchant statistics where values are amounts
 */
class MerchantStatisticsAmountResponse
{
    /**
     * @param array<int,MerchantStatisticAmountByBucket> $buckets Statistics kept for a particular fixed time window
     * @param array<int,MerchantStatisticAmountByInterval> $intervals Statistics kept for a particular sliding interval
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
     *     cumulative_amounts: array<int,string>
     *   }>,
     *   intervals: array<int, array{
     *     start_time: array{t_s: int|string},
     *     cumulative_amounts: array<int,string>
     *   }>,
     *   buckets_description?: string|null,
     *   intervals_description?: string|null
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            buckets: array_map(
                fn(array $item) => MerchantStatisticAmountByBucket::createFromArray($item),
                $data['buckets']
            ),
            intervals: array_map(
                fn(array $item) => MerchantStatisticAmountByInterval::createFromArray($item),
                $data['intervals']
            ),
            buckets_description: $data['buckets_description'] ?? null,
            intervals_description: $data['intervals_description'] ?? null
        );
    }
}



<?php

namespace Taler\Api\Order\Dto;

/**
 * DTO for order history
 * 
 * @phpstan-type OrderHistoryArray array{
 *   orders: array<array{
 *     order_id: string,
 *     row_id: int,
 *     timestamp: array{t_s: int|string},
 *     amount: string,
 *     summary: string,
 *     refundable: bool,
 *     paid: bool
 *   }>
 * }
 */
class OrderHistory
{
    /**
     * @param array<OrderHistoryEntry> $orders Timestamp-sorted array of all orders matching the query
     */
    public function __construct(
        public readonly array $orders,
    ) {
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param array<string, mixed> $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            orders: array_map(
                static fn (array $order) => OrderHistoryEntry::createFromArray($order),
                $data['orders']
            )
        );
    }
} 
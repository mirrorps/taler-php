<?php

namespace Taler\Api\Order\Dto;

use Taler\Api\Dto\Timestamp;

/**
 * DTO for order history entry
 * 
 * @phpstan-type OrderHistoryEntryArray array{
 *   order_id: string,
 *   row_id: int,
 *   timestamp: array{t_s: int|string},
 *   amount: string,
 *   summary: string,
 *   refundable: bool,
 *   paid: bool
 * }
 */
class OrderHistoryEntry
{
    /**
     * @param string $order_id Order ID of the transaction related to this entry
     * @param int $row_id Row ID of the order in the database
     * @param Timestamp $timestamp When the order was created
     * @param string $amount The amount of money the order is for
     * @param string $summary The summary of the order
     * @param bool $refundable Whether some part of the order is refundable
     * @param bool $paid Whether the order has been paid or not
     */
    public function __construct(
        public readonly string $order_id,
        public readonly int $row_id,
        public readonly Timestamp $timestamp,
        public readonly string $amount,
        public readonly string $summary,
        public readonly bool $refundable,
        public readonly bool $paid,
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
            order_id: $data['order_id'],
            row_id: $data['row_id'],
            timestamp: Timestamp::createFromArray($data['timestamp']),
            amount: $data['amount'],
            summary: $data['summary'],
            refundable: $data['refundable'],
            paid: $data['paid']
        );
    }
} 
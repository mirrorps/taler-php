<?php

namespace Taler\Api\Order\Dto;

use Taler\Api\Dto\Timestamp;

/**
 * DTO for check payment unpaid response
 * 
 * @see https://docs.taler.net/core/api-merchant.html#tsref-type-CheckPaymentUnpaidResponse
 * 
 * @phpstan-type CheckPaymentUnpaidResponseArray array{
 *   order_status: "unpaid",
 *   taler_pay_uri: string,
 *   creation_time: array{t_s: int|string},
 *   summary: string,
 *   total_amount?: string,
 *   already_paid_order_id?: string,
 *   already_paid_fulfillment_url?: string,
 *   order_status_url: string
 * }
 */
class CheckPaymentUnpaidResponse
{
    /**
     * @param string $order_status The order was neither claimed nor paid (always "unpaid")
     * @param string $taler_pay_uri URI that the wallet must process to complete the payment
     * @param Timestamp $creation_time When was the order created
     * @param string $summary Order summary text
     * @param string|null $total_amount Total amount of the order (to be paid by the customer). Will be undefined for unpaid v1 orders
     * @param string|null $already_paid_order_id Alternative order ID which was paid for already in the same session
     * @param string|null $already_paid_fulfillment_url Fulfillment URL of an already paid order
     * @param string $order_status_url Status URL, can be used as a redirect target for the browser
     */
    public function __construct(
        public readonly string $order_status,
        public readonly string $taler_pay_uri,
        public readonly Timestamp $creation_time,
        public readonly string $summary,
        public readonly ?string $total_amount,
        public readonly ?string $already_paid_order_id,
        public readonly ?string $already_paid_fulfillment_url,
        public readonly string $order_status_url,
    ) {
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param CheckPaymentUnpaidResponseArray $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            order_status: $data['order_status'],
            taler_pay_uri: $data['taler_pay_uri'],
            creation_time: Timestamp::fromArray($data['creation_time']),
            summary: $data['summary'],
            total_amount: $data['total_amount'] ?? null,
            already_paid_order_id: $data['already_paid_order_id'] ?? null,
            already_paid_fulfillment_url: $data['already_paid_fulfillment_url'] ?? null,
            order_status_url: $data['order_status_url']
        );
    }
} 
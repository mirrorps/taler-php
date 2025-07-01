<?php

namespace Taler\Api\Order\Dto;

class StatusUnpaidResponse
{
    /**
     * @param string $taler_pay_uri URI that the wallet must process to complete the payment
     * @param string|null $fulfillment_url Status URL, can be used as a redirect target for the browser
     * @param string|null $already_paid_order_id Alternative order ID which was paid for already in the same session
     */
    public function __construct(
        public readonly string $taler_pay_uri,
        public readonly ?string $fulfillment_url = null,
        public readonly ?string $already_paid_order_id = null
    ) {}

    /**
     * @param array{
     *     taler_pay_uri: string,
     *     fulfillment_url?: string|null,
     *     already_paid_order_id?: string|null
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['taler_pay_uri'],
            $data['fulfillment_url'] ?? null,
            $data['already_paid_order_id'] ?? null
        );
    }
} 
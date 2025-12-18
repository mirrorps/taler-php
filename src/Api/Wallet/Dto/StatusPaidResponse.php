<?php

namespace Taler\Api\Wallet\Dto;

class StatusPaidResponse
{
    /**
     * @param bool $refunded Was the payment refunded (even partially, via refund or abort)
     * @param bool $refund_pending Is any amount of the refund still waiting to be picked up
     * @param string $refund_amount Amount that was refunded in total
     * @param string $refund_taken Amount that already taken by the wallet
     */
    public function __construct(
        public readonly bool $refunded,
        public readonly bool $refund_pending,
        public readonly string $refund_amount,
        public readonly string $refund_taken
    ) {}

    /**
     * @param array{
     *     refunded: bool,
     *     refund_pending: bool,
     *     refund_amount: string,
     *     refund_taken: string
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            $data['refunded'],
            $data['refund_pending'],
            $data['refund_amount'],
            $data['refund_taken']
        );
    }
} 
<?php

namespace Taler\Api\Order\Dto;

use Taler\Api\Dto\Timestamp;

class RefundDetails
{
    /**
     * @param string $reason Reason given for the refund
     * @param bool $pending Set to true if a refund is still available for the wallet for this payment
     * @param Timestamp $timestamp When was the refund approved
     * @param string $amount Total amount that was refunded (minus a refund fee)
     */
    public function __construct(
        public readonly string $reason,
        public readonly bool $pending,
        public readonly Timestamp $timestamp,
        public readonly string $amount
    ) {}

    /**
     * @param array{
     *     reason: string,
     *     pending: bool,
     *     timestamp: array{t_s: int},
     *     amount: string
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['reason'],
            $data['pending'],
            Timestamp::fromArray($data['timestamp']),
            $data['amount']
        );
    }
} 
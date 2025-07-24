<?php

namespace Taler\Api\Order\Dto;

use Taler\Exception\TalerException;

/**
 * DTO for refund request data.
 */
final class RefundRequest
{
    /**
     * @param string $refund Amount to be refunded
     * @param string $reason Human-readable refund justification
     * @param bool $validate Whether to validate the data automatically
     */
    public function __construct(
        public readonly string $refund,
        public readonly string $reason,
        bool $validate = true
    ) {
        if ($validate) {
            $this->validate();
        }
    }

    /**
     * Validates the DTO data.
     *
     * @throws TalerException if validation fails
     */
    public function validate(): void
    {
        if (empty($this->refund)) {
            throw new TalerException('Refund amount is required');
        }

        if (empty($this->reason)) {
            throw new TalerException('Refund reason is required');
        }
    }

    /**
     * Creates a new instance from an array.
     *
     * @param array{refund: string, reason: string} $data The data array
     *
     * @return self
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            refund: $data['refund'],
            reason: $data['reason']
        );
    }
} 
<?php

namespace Taler\Api\Order\Dto;

/**
 * Response DTO for legally denied payments.
 *
 * Docs shape:
 * interface PaymentDeniedLegallyResponse {
 *   exchange_base_urls: string[];
 * }
 *
 * No validation for response DTOs.
 */
class PaymentDeniedLegallyResponse
{
    /**
     * @param array<int, string> $exchange_base_urls Base URLs of exchanges that denied the payment
     */
    public function __construct(
        public readonly array $exchange_base_urls,
    ) {}

    /**
     * @param array{exchange_base_urls: array<int, string>} $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            exchange_base_urls: $data['exchange_base_urls']
        );
    }
}



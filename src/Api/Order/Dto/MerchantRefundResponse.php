<?php

namespace Taler\Api\Order\Dto;

/**
 * DTO for merchant refund response data.
 */
final class MerchantRefundResponse
{
    /**
     * @param string $taler_refund_uri URL that the wallet should access to trigger refund processing
     * @param string $h_contract Contract hash for request authentication
     */
    public function __construct(
        public readonly string $taler_refund_uri,
        public readonly string $h_contract
    ) {
    }

    /**
     * Creates a new instance from an array.
     *
     * @param array{taler_refund_uri: string, h_contract: string} $data The data array
     *
     * @return self
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            taler_refund_uri: $data['taler_refund_uri'],
            h_contract: $data['h_contract']
        );
    }
} 
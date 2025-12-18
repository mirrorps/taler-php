<?php

namespace Taler\Api\Order\Dto;

class TransactionWireReport
{
    /**
     * @param int $code Numerical error code
     * @param string $hint Human-readable error description
     * @param int $exchange_code Numerical error code from the exchange
     * @param int $exchange_http_status HTTP status code received from the exchange
     * @param string $coin_pub Public key of the coin for which we got the exchange error
     */
    public function __construct(
        public readonly int $code,
        public readonly string $hint,
        public readonly int $exchange_code,
        public readonly int $exchange_http_status,
        public readonly string $coin_pub
    ) {}

    /**
     * @param array{
     *     code: int,
     *     hint: string,
     *     exchange_code: int,
     *     exchange_http_status: int,
     *     coin_pub: string
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            $data['code'],
            $data['hint'],
            $data['exchange_code'],
            $data['exchange_http_status'],
            $data['coin_pub']
        );
    }
} 
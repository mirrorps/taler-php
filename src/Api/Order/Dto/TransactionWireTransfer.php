<?php

namespace Taler\Api\Order\Dto;

use Taler\Api\Dto\Timestamp;

class TransactionWireTransfer
{
    /**
     * @param string $exchange_url Responsible exchange
     * @param string $wtid 32-byte wire transfer identifier
     * @param Timestamp $execution_time Execution time of the wire transfer
     * @param string $amount Total amount that has been wire transferred to the merchant
     * @param bool $confirmed Was this transfer confirmed by the merchant via the POST /transfers API
     */
    public function __construct(
        public readonly string $exchange_url,
        public readonly string $wtid,
        public readonly Timestamp $execution_time,
        public readonly string $amount,
        public readonly bool $confirmed
    ) {}

    /**
     * @param array{
     *     exchange_url: string,
     *     wtid: string,
     *     execution_time: array{t_s: int},
     *     amount: string,
     *     confirmed: bool
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['exchange_url'],
            $data['wtid'],
            Timestamp::fromArray($data['execution_time']),
            $data['amount'],
            $data['confirmed']
        );
    }
} 
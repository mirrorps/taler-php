<?php

namespace Taler\Api\WireTransfers\Dto;

use Taler\Api\Dto\Timestamp;

/**
 * Details of a single wire transfer as returned by the merchant backend
 *
 * @see https://docs.taler.net/core/api-merchant.html#get-[-instances-$INSTANCE]-private-transfers
 */
class TransferDetails
{
    public function __construct(
        public readonly string $credit_amount,
        public readonly string $wtid,
        public readonly string $payto_uri,
        public readonly string $exchange_url,
        public readonly int $transfer_serial_id,
        public readonly Timestamp $execution_time,
        public readonly ?bool $verified = null,
        public readonly ?bool $confirmed = null,
        public readonly ?bool $expected = null,
    ) {}

    /**
     * @param array{
     *     credit_amount: string,
     *     wtid: string,
     *     payto_uri: string,
     *     exchange_url: string,
     *     transfer_serial_id: int,
     *     execution_time: array{t_s: int|string},
     *     verified?: bool|null,
     *     confirmed?: bool|null,
     *     expected?: bool|null
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            credit_amount: $data['credit_amount'],
            wtid: $data['wtid'],
            payto_uri: $data['payto_uri'],
            exchange_url: $data['exchange_url'],
            transfer_serial_id: $data['transfer_serial_id'],
            execution_time: Timestamp::fromArray($data['execution_time']),
            verified: $data['verified'] ?? null,
            confirmed: $data['confirmed'] ?? null,
            expected: $data['expected'] ?? null,
        );
    }
}



<?php

namespace Taler\Api\WireTransfers\Dto;

/**
 * List of wire transfers returned by the merchant backend
 *
 * @see https://docs.taler.net/core/api-merchant.html#get-[-instances-$INSTANCE]-private-transfers
 */
class TransfersList
{
    /**
     * @param array<TransferDetails> $transfers
     */
    public function __construct(
        public readonly array $transfers
    ) {}

    /**
     * @param array{transfers: array<int, array<string, mixed>>} $data
     */
    public static function createFromArray(array $data): self
    {
        /** @var array<int, array{
         *     credit_amount: string,
         *     wtid: string,
         *     payto_uri: string,
         *     exchange_url: string,
         *     transfer_serial_id: int,
         *     execution_time: array{t_s: int|string},
         *     verified?: bool|null,
         *     confirmed?: bool|null,
         *     expected?: bool|null
         * }> $rows
         */
        $rows = $data['transfers'];

        return new self(
            transfers: array_map(
                static fn(array $row): TransferDetails => TransferDetails::createFromArray($row),
                $rows
            )
        );
    }
}



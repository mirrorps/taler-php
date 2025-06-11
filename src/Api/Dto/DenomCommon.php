<?php

namespace Taler\Api\Dto;

/**
 * DTO for common denomination key data
 * 
 * @see https://docs.taler.net/core/api-exchange.html
 */
class DenomCommon
{
    /**
     * @param string $master_sig Signature of TALER_DenominationKeyValidityPS
     * @param Timestamp $stamp_start When does the denomination key become valid?
     * @param Timestamp $stamp_expire_withdraw When is it no longer possible to withdraw coins of this denomination?
     * @param Timestamp $stamp_expire_deposit When is it no longer possible to deposit coins of this denomination?
     * @param Timestamp $stamp_expire_legal Timestamp indicating by when legal disputes relating to these coins must be settled
     * @param bool|null $lost Set to true if the exchange somehow "lost" the private key
     */
    public function __construct(
        public readonly string $master_sig,
        public readonly Timestamp $stamp_start,
        public readonly Timestamp $stamp_expire_withdraw,
        public readonly Timestamp $stamp_expire_deposit,
        public readonly Timestamp $stamp_expire_legal,
        public readonly ?bool $lost = null,
    ) {
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param array{
     *     master_sig: string,
     *     stamp_start: array{t_s: int|string},
     *     stamp_expire_withdraw: array{t_s: int|string},
     *     stamp_expire_deposit: array{t_s: int|string},
     *     stamp_expire_legal: array{t_s: int|string},
     *     lost?: bool
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            master_sig: $data['master_sig'],
            stamp_start: Timestamp::fromArray($data['stamp_start']),
            stamp_expire_withdraw: Timestamp::fromArray($data['stamp_expire_withdraw']),
            stamp_expire_deposit: Timestamp::fromArray($data['stamp_expire_deposit']),
            stamp_expire_legal: Timestamp::fromArray($data['stamp_expire_legal']),
            lost: $data['lost'] ?? null
        );
    }
} 
<?php

namespace Taler\Api\Dto;

/**
 * Represents a future exchange's signing key with its validity periods and security module signature.
 */
class FutureSignKey
{
    /**
     * Constructor for FutureSignKey.
     *
     * @param string $key The actual exchange's EdDSA signing public key
     * @param Timestamp $stamp_start Initial validity date for the signing key
     * @param Timestamp $stamp_expire Date when the exchange will stop using the signing key
     * @param Timestamp $stamp_end Date when all signatures made by the signing key expire
     * @param string $signkey_secmod_sig Signature over TALER_SigningKeyAnnouncementPS by the signkey security module
     */
    public function __construct(
        public readonly string $key,
        public readonly Timestamp $stamp_start,
        public readonly Timestamp $stamp_expire,
        public readonly Timestamp $stamp_end,
        public readonly string $signkey_secmod_sig
    ) {
    }

    /**
     * Create an instance from an array.
     *
     * @param array{
     *     key: string,
     *     stamp_start: array{t_s: int|string},
     *     stamp_expire: array{t_s: int|string},
     *     stamp_end: array{t_s: int|string},
     *     signkey_secmod_sig: string
     * } $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['key'],
            Timestamp::fromArray($data['stamp_start']),
            Timestamp::fromArray($data['stamp_expire']),
            Timestamp::fromArray($data['stamp_end']),
            $data['signkey_secmod_sig']
        );
    }
} 
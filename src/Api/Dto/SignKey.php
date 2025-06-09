<?php

namespace Taler\Api\Dto;

/**
 * Represents an exchange's signing key with its validity periods and master signature.
 */
class SignKey
{
    /**
     * Constructor for SignKey.
     *
     * @param string $key The actual exchange's EdDSA signing public key
     * @param string $stamp_start Initial validity date for the signing key
     * @param string $stamp_expire Date when the exchange will stop using the signing key
     * @param string $stamp_end Date when all signatures made by the signing key expire
     * @param string $master_sig Signature over key and stamp_expire by the exchange master key
     */
    public function __construct(
        public readonly string $key,
        public readonly string $stamp_start,
        public readonly string $stamp_expire,
        public readonly string $stamp_end,
        public readonly string $master_sig
    ) {
    }

    /**
     * Create an instance from an array.
     *
     * @param array{
     *     key: string,
     *     stamp_start: string,
     *     stamp_expire: string,
     *     stamp_end: string,
     *     master_sig: string
     * } $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['key'],
            $data['stamp_start'],
            $data['stamp_expire'],
            $data['stamp_end'],
            $data['master_sig']
        );
    }
} 
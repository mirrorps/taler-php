<?php

namespace Taler\Api\BankAccounts\Dto;

/**
 * Detailed bank account DTO returned by GET private/accounts/$H_WIRE.
 * Note: No validation is performed as requested.
 *
 * interface BankAccountDetail {
 *   payto_uri: string;
 *   h_wire: string;
 *   salt: string;
 *   credit_facade_url?: string;
 *   active: boolean;
 * }
 */
class BankAccountDetail
{
    public function __construct(
        public readonly string $payto_uri,
        public readonly string $h_wire,
        public readonly string $salt,
        public readonly bool $active,
        public readonly ?string $credit_facade_url = null
    ) {
    }

    /**
     * @param array{
     *   payto_uri: string,
     *   h_wire: string,
     *   salt: string,
     *   active: bool,
     *   credit_facade_url?: string|null
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            payto_uri: $data['payto_uri'],
            h_wire: $data['h_wire'],
            salt: $data['salt'],
            active: (bool)$data['active'],
            credit_facade_url: $data['credit_facade_url'] ?? null
        );
    }
}



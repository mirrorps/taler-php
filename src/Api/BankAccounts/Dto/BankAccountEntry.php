<?php

namespace Taler\Api\BankAccounts\Dto;

/**
 * Represents a bank account entry in the merchant instance.
 *
 * @see https://docs.taler.net/core/api-merchant.html#get-[-instances-$INSTANCE]-private-accounts
 */
class BankAccountEntry
{
    /**
     * @param string $payto_uri The payto URI of the bank account
     * @param string $h_wire Hash over the wire details
     * @param bool $active Whether this bank account is active
     * @param bool $validate Whether to validate inputs
     */
    public function __construct(
        public readonly string $payto_uri,
        public readonly string $h_wire,
        public readonly bool $active,
        bool $validate = true
    ) {
        if ($validate) {
            $this->validate();
        }
    }

    /**
     * @param array{payto_uri: string, h_wire: string, active: bool} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            payto_uri: $data['payto_uri'],
            h_wire: $data['h_wire'],
            active: (bool)$data['active']
        );
    }

    public function validate(): void
    {
        if ($this->payto_uri === '') {
            throw new \InvalidArgumentException('payto_uri must not be empty');
        }
        if ($this->h_wire === '') {
            throw new \InvalidArgumentException('h_wire must not be empty');
        }
    }
}



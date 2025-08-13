<?php

namespace Taler\Api\BankAccounts\Dto;

/**
 * DTO for add bank account response.
 * Contains the hash of the wire details and the salt.
 */
class AccountAddResponse
{
    /**
     * @param string $h_wire Hash over the wire details
     * @param string $salt Salt used when hashing the wire details
     * @param bool $validate Whether to validate inputs
     */
    public function __construct(
        public readonly string $h_wire,
        public readonly string $salt,
        bool $validate = true
    ) {
        if ($validate) {
            $this->validate();
        }
    }

    /**
     * @param array{h_wire: string, salt: string} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            h_wire: $data['h_wire'],
            salt: $data['salt']
        );
    }

    public function validate(): void
    {
        if ($this->h_wire === '' || $this->salt === '') {
            throw new \InvalidArgumentException('h_wire and salt must not be empty');
        }
    }
}



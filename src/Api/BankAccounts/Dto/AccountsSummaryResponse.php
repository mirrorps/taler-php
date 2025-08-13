<?php

namespace Taler\Api\BankAccounts\Dto;

/**
 * DTO for the list of bank accounts response (AccountsSummaryResponse in docs).
 */
class AccountsSummaryResponse
{
    /**
     * @param array<BankAccountEntry> $accounts
     * @param bool $validate
     */
    public function __construct(
        public readonly array $accounts,
        bool $validate = true
    ) {
        if ($validate) {
            $this->validate();
        }
    }

    /**
     * @param array{accounts: array<int, array{payto_uri: string, h_wire: string, active: bool}>} $data
     */
    public static function fromArray(array $data): self
    {
        $accounts = array_map(
            static fn(array $a) => BankAccountEntry::fromArray($a),
            $data['accounts']
        );

        return new self($accounts);
    }

    public function validate(): void
    {
        // Intentionally left blank. Constructor PHPDoc ensures type safety for $accounts.
    }
}



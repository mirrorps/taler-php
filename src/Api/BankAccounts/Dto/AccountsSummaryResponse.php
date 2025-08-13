<?php

namespace Taler\Api\BankAccounts\Dto;

/**
 * DTO for the list of bank accounts response (AccountsSummaryResponse in docs).
 */
class AccountsSummaryResponse
{
    /**
     * @param array<BankAccountEntry> $accounts
     */
    public function __construct(
        public readonly array $accounts,
    ) {
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
}



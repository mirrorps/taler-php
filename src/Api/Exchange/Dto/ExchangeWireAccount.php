<?php

namespace Taler\Api\Exchange\Dto;

use Taler\Api\Contract\AccountRestriction;
use Taler\Api\Dto\DenyAllAccountRestriction;
use Taler\Api\Dto\RegexAccountRestriction;

/**
 * DTO for the exchange wire account details
 *
 * @see https://docs.taler.net/core/api-exchange.html
 */
class ExchangeWireAccount
{
    /**
     * @param string $payto_uri Full payto:// URI identifying the account and wire method
     * @param string $master_sig Signature using the exchange's offline key over a TALER_MasterWireDetailsPS with purpose TALER_SIGNATURE_MASTER_WIRE_DETAILS
     * @param array<int, AccountRestriction> $credit_restrictions Restrictions that apply to bank accounts that would send funds to the exchange
     * @param array<int, AccountRestriction> $debit_restrictions Restrictions that apply to bank accounts that would receive funds from the exchange
     * @param string|null $conversion_url URI to convert amounts from or to the currency used by this wire account of the exchange
     * @param string|null $bank_label Display label wallets should use to show this bank account (since protocol v19)
     * @param int|null $priority Signed integer with the display priority for this bank account, 0 if missing (since protocol v19)
     */
    public function __construct(
        public readonly string $payto_uri,
        public readonly string $master_sig,
        public readonly array $credit_restrictions = [],
        public readonly array $debit_restrictions = [],
        public readonly ?string $conversion_url = null,
        public readonly ?string $bank_label = null,
        public readonly ?int $priority = 0,
    ) {
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param array{
     *     payto_uri: string,
     *     master_sig: string,
     *     credit_restrictions?: array<int, array{type: 'deny'}|array{
     *         type: 'regex',
     *         payto_regex: string,
     *         human_hint: string,
     *         human_hint_i18n?: array<string, string>|null
     *     }>,
     *     debit_restrictions?: array<int, array{type: 'deny'}|array{
     *         type: 'regex',
     *         payto_regex: string,
     *         human_hint: string,
     *         human_hint_i18n?: array<string, string>|null
     *     }>,
     *     conversion_url?: string|null,
     *     bank_label?: string|null,
     *     priority?: int|null
     * } $data
     */
    public static function fromArray(array $data): self
    {
        $creditRestrictions = [];
        foreach ($data['credit_restrictions'] ?? [] as $restriction) {
            if ($restriction['type'] === 'regex') {
                $creditRestrictions[] = RegexAccountRestriction::fromArray($restriction);
            } else {
                $creditRestrictions[] = DenyAllAccountRestriction::fromArray($restriction);
            }
        }

        $debitRestrictions = [];
        foreach ($data['debit_restrictions'] ?? [] as $restriction) {
            if ($restriction['type'] === 'regex') {
                $debitRestrictions[] = RegexAccountRestriction::fromArray($restriction);
            } else {
                $debitRestrictions[] = DenyAllAccountRestriction::fromArray($restriction);
            }
        }

        return new self(
            payto_uri: $data['payto_uri'],
            master_sig: $data['master_sig'],
            credit_restrictions: $creditRestrictions,
            debit_restrictions: $debitRestrictions,
            conversion_url: $data['conversion_url'] ?? null,
            bank_label: $data['bank_label'] ?? null,
            priority: $data['priority'] ?? 0
        );
    }
} 
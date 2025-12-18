<?php

namespace Taler\Api\Contract;

/**
 * Interface for account restrictions in the exchange wire account details.
 * Can be either a RegexAccountRestriction or DenyAllAccountRestriction.
 *
 * @see https://docs.taler.net/core/api-exchange.html
 */
interface AccountRestriction
{
    /**
     * Returns the type of the restriction
     */
    public function getType(): string;

    /**
     * Creates a new instance from an array of data
     *
     * @param array{type: string} & (
     *     array{type: 'regex', payto_regex: string, human_hint: string, human_hint_i18n?: array<string, string>|null} |
     *     array{type: 'deny'}
     * ) $data
     * @return self Either RegexAccountRestriction or DenyAllAccountRestriction
     */
    public static function createFromArray(array $data): self;
} 
<?php

namespace Taler\Api\BankAccounts\Dto;

/**
 * DTO for updating a bank account (PATCH private/accounts/$H_WIRE).
 *
 * Fields are optional; only provided fields will be updated by the backend.
 *
 * Docs: https://docs.taler.net/core/api-merchant.html#patch-[-instances-$INSTANCE]-private-accounts-$H_WIRE
 */
class AccountPatchDetails
{
    /**
     * @param string|null $credit_facade_url Optional facade URL for automated credit operations
     * @param FacadeCredentials|null $credit_facade_credentials Optional credentials for the facade. Use NoFacadeCredentials to remove.
     * @param bool $validate Whether to validate inputs
     */
    public function __construct(
        public readonly ?string $credit_facade_url = null,
        public readonly ?FacadeCredentials $credit_facade_credentials = null,
        bool $validate = true
    ) {
        if ($validate) {
            $this->validate();
        }
    }

    /**
     * @param array{
     *     credit_facade_url?: string|null,
     *     credit_facade_credentials?: array{
     *         type: 'none'|'basic',
     *         username?: string,
     *         password?: string
     *     }|null
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        $creds = null;
        if (array_key_exists('credit_facade_credentials', $data)) {
            $raw = $data['credit_facade_credentials'];
            if (is_array($raw)) {
                /** @var array{type: 'none'|'basic', username?: string, password?: string} $cc */
                $cc = $raw;
                $type = $cc['type'];
                $creds = match ($type) {
                    'basic' => new BasicAuthFacadeCredentials(
                        username: (string)($cc['username'] ?? ''),
                        password: (string)($cc['password'] ?? '')
                    ),
                    default => new NoFacadeCredentials(),
                };
            } else {
                $creds = null;
            }
        }

        return new self(
            credit_facade_url: $data['credit_facade_url'] ?? null,
            credit_facade_credentials: $creds
        );
    }

    public function validate(): void
    {
        if ($this->credit_facade_credentials instanceof BasicAuthFacadeCredentials) {
            if ($this->credit_facade_credentials->username === '' || $this->credit_facade_credentials->password === '') {
                throw new \InvalidArgumentException('Basic credentials require non-empty username and password');
            }
        }
    }
}




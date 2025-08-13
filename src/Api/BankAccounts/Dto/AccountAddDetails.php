<?php

namespace Taler\Api\BankAccounts\Dto;

/**
 * DTO for adding a bank account to a merchant instance.
 *
 * See Merchant API: Bank Accounts - Add account
 * Docs: https://docs.taler.net/core/api-merchant.html#bank-accounts
 */
class AccountAddDetails
{
    /**
     * @param string $payto_uri The payto URI of the bank account
     * @param string|null $credit_facade_url Optional facade URL for automated credit operations
     * @param FacadeCredentials|null $credit_facade_credentials Optional credentials for the facade
     * @param bool $validate Whether to validate inputs
     */
    public function __construct(
        public readonly string $payto_uri,
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
     *     payto_uri: string,
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
        if (isset($data['credit_facade_credentials'])) {
            /** @var array{type: 'none'|'basic', username?: string, password?: string} $cc */
            $cc = $data['credit_facade_credentials'];
            $type = $cc['type'];
            $creds = match ($type) {
                'basic' => new BasicAuthFacadeCredentials(
                    username: (string)($cc['username'] ?? ''),
                    password: (string)($cc['password'] ?? '')
                ),
                default => new NoFacadeCredentials(),
            };
        }

        return new self(
            payto_uri: $data['payto_uri'],
            credit_facade_url: $data['credit_facade_url'] ?? null,
            credit_facade_credentials: $creds
        );
    }

    /**
     * Validate the input data.
     */
    public function validate(): void
    {
        if ($this->payto_uri === '') {
            throw new \InvalidArgumentException('payto_uri must not be empty');
        }

        if ($this->credit_facade_credentials instanceof BasicAuthFacadeCredentials) {
            if ($this->credit_facade_credentials->username === '' || $this->credit_facade_credentials->password === '') {
                throw new \InvalidArgumentException('Basic credentials require non-empty username and password');
            }
        }
    }
}



<?php

namespace Taler\Api\BankAccounts;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\BankAccounts\Dto\AccountAddDetails;
use Taler\Api\BankAccounts\Dto\AccountAddResponse;
use Taler\Api\Base\AbstractApiClient;
use Taler\Exception\TalerException;

class BankAccountClient extends AbstractApiClient
{
    /**
     * Create a bank account for the merchant instance.
     *
     * @param AccountAddDetails $details
     * @param array<string, string> $headers Optional request headers
     * @return AccountAddResponse|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     * @see https://docs.taler.net/core/api-merchant.html#bank-accounts
     */
    public function createAccount(AccountAddDetails $details, array $headers = []): AccountAddResponse|array
    {
        return Actions\CreateAccount::run($this, $details, $headers);
    }

    /**
     * Async variant for creating a bank account.
     *
     * @param AccountAddDetails $details
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function createAccountAsync(AccountAddDetails $details, array $headers = []): mixed
    {
        return Actions\CreateAccount::runAsync($this, $details, $headers);
    }
}



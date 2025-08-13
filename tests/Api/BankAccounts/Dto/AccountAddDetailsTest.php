<?php

namespace Taler\Tests\Api\BankAccounts\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\BankAccounts\Dto\AccountAddDetails;
use Taler\Api\BankAccounts\Dto\BasicAuthFacadeCredentials;
use Taler\Api\BankAccounts\Dto\NoFacadeCredentials;

class AccountAddDetailsTest extends TestCase
{
    public function testConstructAndValidation(): void
    {
        $creds = new BasicAuthFacadeCredentials('user', 'pass');
        $dto = new AccountAddDetails('payto://iban/DE123', 'https://facade.example', $creds);

        $this->assertSame('payto://iban/DE123', $dto->payto_uri);
        $this->assertSame('https://facade.example', $dto->credit_facade_url);
        $this->assertInstanceOf(BasicAuthFacadeCredentials::class, $dto->credit_facade_credentials);
    }

    public function testCreateFromArrayWithBasicCreds(): void
    {
        $dto = AccountAddDetails::createFromArray([
            'payto_uri' => 'payto://iban/DE999',
            'credit_facade_url' => 'https://facade.example',
            'credit_facade_credentials' => [
                'type' => 'basic',
                'username' => 'u',
                'password' => 'p'
            ]
        ]);

        $this->assertSame('payto://iban/DE999', $dto->payto_uri);
        $this->assertSame('https://facade.example', $dto->credit_facade_url);
        $this->assertInstanceOf(BasicAuthFacadeCredentials::class, $dto->credit_facade_credentials);
    }

    public function testCreateFromArrayWithNoCreds(): void
    {
        $dto = AccountAddDetails::createFromArray([
            'payto_uri' => 'payto://iban/DE999',
            'credit_facade_credentials' => [
                'type' => 'none'
            ]
        ]);

        $this->assertInstanceOf(NoFacadeCredentials::class, $dto->credit_facade_credentials);
    }

    public function testValidationFailsOnEmptyPayto(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new AccountAddDetails('');
    }

    public function testValidationFailsOnEmptyBasicCreds(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new AccountAddDetails('payto://iban/DE123', null, new BasicAuthFacadeCredentials('', ''));
    }
}



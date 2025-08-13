<?php

namespace Taler\Tests\Api\BankAccounts\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\BankAccounts\Dto\AccountPatchDetails;
use Taler\Api\BankAccounts\Dto\BasicAuthFacadeCredentials;
use Taler\Api\BankAccounts\Dto\NoFacadeCredentials;

class AccountPatchDetailsTest extends TestCase
{
    public function testConstructAndValidation(): void
    {
        $dto = new AccountPatchDetails(
            credit_facade_url: 'https://facade.example',
            credit_facade_credentials: new BasicAuthFacadeCredentials('user', 'pass')
        );

        $this->assertSame('https://facade.example', $dto->credit_facade_url);
        $this->assertInstanceOf(BasicAuthFacadeCredentials::class, $dto->credit_facade_credentials);
    }

    public function testCreateFromArrayWithBasic(): void
    {
        $dto = AccountPatchDetails::createFromArray([
            'credit_facade_url' => 'https://facade.example',
            'credit_facade_credentials' => [
                'type' => 'basic',
                'username' => 'u',
                'password' => 'p',
            ],
        ]);

        $this->assertSame('https://facade.example', $dto->credit_facade_url);
        $this->assertInstanceOf(BasicAuthFacadeCredentials::class, $dto->credit_facade_credentials);
    }

    public function testCreateFromArrayWithNone(): void
    {
        $dto = AccountPatchDetails::createFromArray([
            'credit_facade_credentials' => [
                'type' => 'none',
            ],
        ]);

        $this->assertNull($dto->credit_facade_url);
        $this->assertInstanceOf(NoFacadeCredentials::class, $dto->credit_facade_credentials);
    }

    public function testValidationFailsForEmptyBasic(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new AccountPatchDetails(
            credit_facade_url: null,
            credit_facade_credentials: new BasicAuthFacadeCredentials('', '')
        );
    }
}




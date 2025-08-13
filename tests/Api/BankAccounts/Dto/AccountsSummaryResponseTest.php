<?php

namespace Taler\Tests\Api\BankAccounts\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\BankAccounts\Dto\AccountsSummaryResponse;
use Taler\Api\BankAccounts\Dto\BankAccountEntry;

class AccountsSummaryResponseTest extends TestCase
{
    public function testFromArray(): void
    {
        $data = [
            'accounts' => [
                ['payto_uri' => 'payto://iban/DE1', 'h_wire' => 'h1', 'active' => true],
                ['payto_uri' => 'payto://iban/DE2', 'h_wire' => 'h2', 'active' => false],
            ]
        ];

        $response = AccountsSummaryResponse::fromArray($data);
        $this->assertCount(2, $response->accounts);
        $this->assertInstanceOf(BankAccountEntry::class, $response->accounts[0]);
        $this->assertSame('payto://iban/DE1', $response->accounts[0]->payto_uri);
        $this->assertFalse($response->accounts[1]->active);
    }
}



<?php

namespace Taler\Tests\Api\BankAccounts\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\BankAccounts\Dto\BankAccountEntry;

class BankAccountEntryTest extends TestCase
{
    public function testFromArrayAndValidation(): void
    {
        $entry = BankAccountEntry::createFromArray([
            'payto_uri' => 'payto://iban/DE123',
            'h_wire' => 'hash',
            'active' => true,
        ]);

        $this->assertSame('payto://iban/DE123', $entry->payto_uri);
        $this->assertSame('hash', $entry->h_wire);
        $this->assertTrue($entry->active);
    }

    public function testValidationFails(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new BankAccountEntry('', '', true);
    }
}



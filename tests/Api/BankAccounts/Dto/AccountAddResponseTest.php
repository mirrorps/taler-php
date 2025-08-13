<?php

namespace Taler\Tests\Api\BankAccounts\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\BankAccounts\Dto\AccountAddResponse;

class AccountAddResponseTest extends TestCase
{
    public function testFromArray(): void
    {
        $data = [
            'h_wire' => 'hash123',
            'salt' => 'salt456'
        ];

        $dto = AccountAddResponse::fromArray($data);
        $this->assertSame('hash123', $dto->h_wire);
        $this->assertSame('salt456', $dto->salt);
    }

}



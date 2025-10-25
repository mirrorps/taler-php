<?php

namespace Taler\Tests\Api\Config\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Config\Dto\ExchangeConfigInfo;

class ExchangeConfigInfoTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = [
            'base_url' => 'https://exchange.example.com',
            'currency' => 'EUR',
            'master_pub' => 'PUBKEY'
        ];

        $info = ExchangeConfigInfo::createFromArray($data);

        $this->assertSame('https://exchange.example.com', $info->base_url);
        $this->assertSame('EUR', $info->currency);
        $this->assertSame('PUBKEY', $info->master_pub);
    }
}



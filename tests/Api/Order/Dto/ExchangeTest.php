<?php

namespace Taler\Tests\Api\Order\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Order\Dto\Exchange;

class ExchangeTest extends TestCase
{
    public function testFromArray(): void
    {
        $data = [
            'url' => 'https://exchange.test.taler.net/',
            'priority' => 1024,
            'master_pub' => 'test_master_public_key',
            'max_contribution' => '1000.00'
        ];

        $exchange = Exchange::fromArray($data);

        $this->assertInstanceOf(Exchange::class, $exchange);
        $this->assertEquals($data['url'], $exchange->url);
        $this->assertEquals($data['priority'], $exchange->priority);
        $this->assertEquals($data['master_pub'], $exchange->master_pub);
        $this->assertEquals($data['max_contribution'], $exchange->max_contribution);
    }

    public function testFromArrayWithoutOptionalFields(): void
    {
        $data = [
            'url' => 'https://exchange.test.taler.net/',
            'priority' => 512,
            'master_pub' => 'test_master_public_key'
        ];

        $exchange = Exchange::fromArray($data);

        $this->assertInstanceOf(Exchange::class, $exchange);
        $this->assertEquals($data['url'], $exchange->url);
        $this->assertEquals($data['priority'], $exchange->priority);
        $this->assertEquals($data['master_pub'], $exchange->master_pub);
        $this->assertNull($exchange->max_contribution);
    }
} 
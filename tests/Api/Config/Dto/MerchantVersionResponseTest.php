<?php

namespace Taler\Tests\Api\Config\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Config\Dto\ExchangeConfigInfo;
use Taler\Api\Config\Dto\MerchantVersionResponse;
use Taler\Api\Dto\CurrencySpecification;

class MerchantVersionResponseTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = [
            'version' => '42:1:0',
            'name' => 'taler-merchant',
            'implementation' => 'urn:gnu:taler:merchant:v1',
            'currency' => 'EUR',
            'currencies' => [
                'EUR' => [
                    'name' => 'Euro',
                    'currency' => 'EUR',
                    'num_fractional_input_digits' => 2,
                    'num_fractional_normal_digits' => 2,
                    'num_fractional_trailing_zero_digits' => 2,
                    'alt_unit_names' => ['0' => '€']
                ]
            ],
            'exchanges' => [
                [
                    'base_url' => 'https://exchange.example.com',
                    'currency' => 'EUR',
                    'master_pub' => 'EXCHANGEPUBKEY'
                ]
            ],
            'have_self_provisioning' => true,
            'have_donau' => false,
            'mandatory_tan_channels' => ['sms', 'email']
        ];

        $resp = MerchantVersionResponse::createFromArray($data);

        $this->assertSame('42:1:0', $resp->version);
        $this->assertSame('taler-merchant', $resp->name);
        $this->assertSame('urn:gnu:taler:merchant:v1', $resp->implementation);
        $this->assertSame('EUR', $resp->currency);
        $this->assertArrayHasKey('EUR', $resp->currencies);
        $this->assertInstanceOf(CurrencySpecification::class, $resp->currencies['EUR']);
        $this->assertCount(1, $resp->exchanges);
        $this->assertInstanceOf(ExchangeConfigInfo::class, $resp->exchanges[0]);
        $this->assertTrue($resp->have_self_provisioning);
        $this->assertFalse($resp->have_donau);
        $this->assertEquals(['sms', 'email'], $resp->mandatory_tan_channels);
    }
}



<?php

namespace Taler\Tests\Api\Order\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Order\Dto\Merchant;
use Taler\Api\Dto\Location;

class MerchantTest extends TestCase
{
    public function testFromArray(): void
    {
        $data = [
            'name' => 'Test Merchant Inc.',
            'email' => 'contact@testmerchant.com',
            'website' => 'https://testmerchant.com',
            'logo' => 'data:image/png;base64,test123',
            'address' => [
                'country' => 'US',
                'country_subdivision' => 'California',
                'town' => 'San Francisco',
                'street' => '123 Test St'
            ],
            'jurisdiction' => [
                'country' => 'US',
                'country_subdivision' => 'Delaware'
            ]
        ];

        $merchant = Merchant::fromArray($data);

        $this->assertInstanceOf(Merchant::class, $merchant);
        $this->assertEquals($data['name'], $merchant->name);
        $this->assertEquals($data['email'], $merchant->email);
        $this->assertEquals($data['website'], $merchant->website);
        $this->assertEquals($data['logo'], $merchant->logo);
        
        $this->assertInstanceOf(Location::class, $merchant->address);
        $this->assertEquals($data['address']['country'], $merchant->address->country);
        $this->assertEquals($data['address']['country_subdivision'], $merchant->address->country_subdivision);
        $this->assertEquals($data['address']['town'], $merchant->address->town);
        $this->assertEquals($data['address']['street'], $merchant->address->street);
        
        $this->assertInstanceOf(Location::class, $merchant->jurisdiction);
        $this->assertEquals($data['jurisdiction']['country'], $merchant->jurisdiction->country);
        $this->assertEquals($data['jurisdiction']['country_subdivision'], $merchant->jurisdiction->country_subdivision);
    }

    public function testFromArrayWithoutOptionalFields(): void
    {
        $data = [
            'name' => 'Test Merchant Inc.'
        ];

        $merchant = Merchant::fromArray($data);

        $this->assertInstanceOf(Merchant::class, $merchant);
        $this->assertEquals($data['name'], $merchant->name);
        $this->assertNull($merchant->email);
        $this->assertNull($merchant->website);
        $this->assertNull($merchant->logo);
        $this->assertNull($merchant->address);
        $this->assertNull($merchant->jurisdiction);
    }
} 
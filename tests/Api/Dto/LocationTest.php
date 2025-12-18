<?php

namespace Taler\Tests\Api\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\Location;

class LocationTest extends TestCase
{
    public function testFromArray(): void
    {
        $data = [
            'country' => 'United States',
            'country_subdivision' => 'California',
            'district' => 'San Francisco',
            'town' => 'San Francisco',
            'town_location' => 'Financial District',
            'post_code' => '94105',
            'street' => 'Market Street',
            'building_name' => 'Salesforce Tower',
            'building_number' => '415',
            'address_lines' => ['Floor 12', 'Suite 1200']
        ];

        $location = Location::createFromArray($data);

        $this->assertInstanceOf(Location::class, $location);
        $this->assertEquals($data['country'], $location->country);
        $this->assertEquals($data['country_subdivision'], $location->country_subdivision);
        $this->assertEquals($data['district'], $location->district);
        $this->assertEquals($data['town'], $location->town);
        $this->assertEquals($data['town_location'], $location->town_location);
        $this->assertEquals($data['post_code'], $location->post_code);
        $this->assertEquals($data['street'], $location->street);
        $this->assertEquals($data['building_name'], $location->building_name);
        $this->assertEquals($data['building_number'], $location->building_number);
        $this->assertEquals($data['address_lines'], $location->address_lines);
    }

    public function testFromArrayWithNullValues(): void
    {
        $data = [
            'country' => 'United States',
            'town' => 'San Francisco'
        ];

        $location = Location::createFromArray($data);

        $this->assertInstanceOf(Location::class, $location);
        $this->assertEquals($data['country'], $location->country);
        $this->assertEquals($data['town'], $location->town);
        $this->assertNull($location->country_subdivision);
        $this->assertNull($location->district);
        $this->assertNull($location->town_location);
        $this->assertNull($location->post_code);
        $this->assertNull($location->street);
        $this->assertNull($location->building_name);
        $this->assertNull($location->building_number);
        $this->assertNull($location->address_lines);
    }

    public function testFromArrayWithEmptyArray(): void
    {
        $location = Location::createFromArray([]);

        $this->assertInstanceOf(Location::class, $location);
        $this->assertNull($location->country);
        $this->assertNull($location->country_subdivision);
        $this->assertNull($location->district);
        $this->assertNull($location->town);
        $this->assertNull($location->town_location);
        $this->assertNull($location->post_code);
        $this->assertNull($location->street);
        $this->assertNull($location->building_name);
        $this->assertNull($location->building_number);
        $this->assertNull($location->address_lines);
    }
} 
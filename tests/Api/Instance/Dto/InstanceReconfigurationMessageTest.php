<?php

namespace Taler\Tests\Api\Instance\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Instance\Dto\InstanceReconfigurationMessage;
use Taler\Api\Dto\Location;
use Taler\Api\Dto\RelativeTime;

class InstanceReconfigurationMessageTest extends TestCase
{
    public function testValidConstruction(): void
    {
        $msg = new InstanceReconfigurationMessage(
            name: 'New Name',
            address: new Location(country: 'DE', town: 'Berlin'),
            jurisdiction: new Location(country: 'DE', town: 'Berlin'),
            use_stefan: true,
            default_wire_transfer_delay: new RelativeTime(86400000000),
            default_pay_delay: new RelativeTime(3600000000),
            email: 'merchant@example.com',
            phone_number: '+49123456789',
            website: 'https://example.com',
            logo: 'data:image/png;base64,abc',
        );

        $this->assertSame('New Name', $msg->name);
        $this->assertTrue($msg->use_stefan);
    }

    public function testCreateFromArray(): void
    {
        $data = [
            'name' => 'New Name',
            'email' => 'merchant@example.com',
            'phone_number' => '+49123456789',
            'website' => 'https://example.com',
            'logo' => 'data:image/png;base64,abc',
            'address' => [
                'country' => 'DE',
                'town' => 'Berlin'
            ],
            'jurisdiction' => [
                'country' => 'DE',
                'town' => 'Berlin'
            ],
            'use_stefan' => true,
            'default_wire_transfer_delay' => ['d_us' => 86400000000],
            'default_pay_delay' => ['d_us' => 3600000000],
        ];

        $msg = InstanceReconfigurationMessage::createFromArray($data);
        $this->assertInstanceOf(InstanceReconfigurationMessage::class, $msg);
        $this->assertSame('New Name', $msg->name);
    }

    public function testValidationEmptyName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new InstanceReconfigurationMessage(
            name: '',
            address: new Location(country: 'DE', town: 'Berlin'),
            jurisdiction: new Location(country: 'DE', town: 'Berlin'),
            use_stefan: true,
            default_wire_transfer_delay: new RelativeTime(1),
            default_pay_delay: new RelativeTime(1),
        );
    }

    public function testValidationInvalidEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new InstanceReconfigurationMessage(
            name: 'Name',
            address: new Location(country: 'DE', town: 'Berlin'),
            jurisdiction: new Location(country: 'DE', town: 'Berlin'),
            use_stefan: true,
            default_wire_transfer_delay: new RelativeTime(1),
            default_pay_delay: new RelativeTime(1),
            email: 'invalid-email'
        );
    }
}



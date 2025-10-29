<?php

namespace Taler\Tests\Api\DonauCharity\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\DonauCharity\Dto\DonauInstance;

class DonauInstanceTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = [
            'donau_instance_serial' => 42,
            'donau_url' => 'https://donau.example',
            'charity_name' => 'Help Org',
            'charity_pub_key' => 'EDDSA_PUB_KEY_STRING',
            'charity_id' => 7,
            'charity_max_per_year' => 'EUR:1000',
            'charity_receipts_to_date' => 'EUR:123.45',
            'current_year' => 2025,
            'donau_keys_json' => ['version' => 1, 'keys' => []],
        ];

        $result = DonauInstance::createFromArray($data);

        $this->assertInstanceOf(DonauInstance::class, $result);
        $this->assertSame(42, $result->donau_instance_serial);
        $this->assertSame('https://donau.example', $result->donau_url);
        $this->assertSame('Help Org', $result->charity_name);
        $this->assertSame('EDDSA_PUB_KEY_STRING', $result->charity_pub_key);
        $this->assertSame(7, $result->charity_id);
        $this->assertSame('EUR:1000', $result->charity_max_per_year);
        $this->assertSame('EUR:123.45', $result->charity_receipts_to_date);
        $this->assertSame(2025, $result->current_year);
        $this->assertIsArray($result->donau_keys_json);
        $this->assertArrayHasKey('version', $result->donau_keys_json);
    }

    public function testCreateFromArrayWithoutOptionalKeys(): void
    {
        $data = [
            'donau_instance_serial' => 1,
            'donau_url' => 'https://donau.example',
            'charity_name' => 'Charity',
            'charity_pub_key' => 'PUBKEY',
            'charity_id' => 99,
            'charity_max_per_year' => 'CHF:500',
            'charity_receipts_to_date' => 'CHF:0',
            'current_year' => 2024,
        ];

        $result = DonauInstance::createFromArray($data);

        $this->assertInstanceOf(DonauInstance::class, $result);
        $this->assertNull($result->donau_keys_json);
    }
}

 


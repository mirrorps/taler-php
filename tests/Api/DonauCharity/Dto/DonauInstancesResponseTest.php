<?php

namespace Taler\Tests\Api\DonauCharity\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\DonauCharity\Dto\DonauInstance;
use Taler\Api\DonauCharity\Dto\DonauInstancesResponse;

class DonauInstancesResponseTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = [
            'donau_instances' => [
                [
                    'donau_instance_serial' => 1,
                    'donau_url' => 'https://a.example',
                    'charity_name' => 'A',
                    'charity_pub_key' => 'PUB_A',
                    'charity_id' => 10,
                    'charity_max_per_year' => 'EUR:100',
                    'charity_receipts_to_date' => 'EUR:5',
                    'current_year' => 2025,
                ],
                [
                    'donau_instance_serial' => 2,
                    'donau_url' => 'https://b.example',
                    'charity_name' => 'B',
                    'charity_pub_key' => 'PUB_B',
                    'charity_id' => 11,
                    'charity_max_per_year' => 'EUR:200',
                    'charity_receipts_to_date' => 'EUR:20',
                    'current_year' => 2025,
                    'donau_keys_json' => ['keys' => []],
                ],
            ],
        ];

        $result = DonauInstancesResponse::createFromArray($data);

        $this->assertInstanceOf(DonauInstancesResponse::class, $result);
        $this->assertCount(2, $result->donau_instances);
        $this->assertInstanceOf(DonauInstance::class, $result->donau_instances[0]);
        $this->assertSame('A', $result->donau_instances[0]->charity_name);
        $this->assertSame(2, $result->donau_instances[1]->donau_instance_serial);
        $this->assertIsArray($result->donau_instances[1]->donau_keys_json);
    }
}

 


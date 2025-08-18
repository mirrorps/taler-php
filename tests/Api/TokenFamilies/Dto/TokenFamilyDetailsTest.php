<?php

namespace Taler\Tests\Api\TokenFamilies\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\RelativeTime;
use Taler\Api\Dto\Timestamp;
use Taler\Api\TokenFamilies\Dto\TokenFamilyDetails;

class TokenFamilyDetailsTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = [
            'slug' => 'family-01',
            'name' => 'Name',
            'description' => 'Desc',
            'description_i18n' => ['en' => 'Desc EN'],
            'extra_data' => ['expected_domains' => ['example.com']],
            'valid_after' => ['t_s' => 1700000000],
            'valid_before' => ['t_s' => 1800000000],
            'duration' => ['d_us' => 60_000_000],
            'validity_granularity' => ['d_us' => 60_000_000],
            'start_offset' => ['d_us' => 0],
            'kind' => 'discount',
            'issued' => 10,
            'used' => 5,
        ];

        $dto = TokenFamilyDetails::createFromArray($data);

        $this->assertSame('family-01', $dto->slug);
        $this->assertSame('Name', $dto->name);
        $this->assertSame('Desc', $dto->description);
        $this->assertIsArray($dto->description_i18n);
        $this->assertIsArray($dto->extra_data);
        $this->assertInstanceOf(Timestamp::class, $dto->valid_after);
        $this->assertInstanceOf(Timestamp::class, $dto->valid_before);
        $this->assertInstanceOf(RelativeTime::class, $dto->duration);
        $this->assertInstanceOf(RelativeTime::class, $dto->validity_granularity);
        $this->assertInstanceOf(RelativeTime::class, $dto->start_offset);
        $this->assertSame('discount', $dto->kind);
        $this->assertSame(10, $dto->issued);
        $this->assertSame(5, $dto->used);
    }
}



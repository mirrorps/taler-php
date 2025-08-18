<?php

namespace Taler\Tests\Api\TokenFamilies\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\RelativeTime;
use Taler\Api\Dto\Timestamp;
use Taler\Api\TokenFamilies\Dto\TokenFamilyCreateRequest;

class TokenFamilyCreateRequestTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = [
            'slug' => 'family-01',
            'name' => 'My Family',
            'description' => 'Desc',
            'description_i18n' => ['en' => 'Desc EN'],
            'extra_data' => ['trusted_domains' => ['example.com']],
            'valid_after' => ['t_s' => 1700000000],
            'valid_before' => ['t_s' => 1800000000],
            'duration' => ['d_us' => 1000000],
            'validity_granularity' => ['d_us' => 60000000],
            'start_offset' => ['d_us' => 0],
            'kind' => 'discount',
        ];

        $dto = TokenFamilyCreateRequest::createFromArray($data);

        $this->assertSame('family-01', $dto->slug);
        $this->assertSame('My Family', $dto->name);
        $this->assertSame('Desc', $dto->description);
        $this->assertIsArray($dto->description_i18n);
        $this->assertIsArray($dto->extra_data);
        $this->assertInstanceOf(Timestamp::class, $dto->valid_after);
        $this->assertInstanceOf(Timestamp::class, $dto->valid_before);
        $this->assertInstanceOf(RelativeTime::class, $dto->duration);
        $this->assertInstanceOf(RelativeTime::class, $dto->validity_granularity);
        $this->assertInstanceOf(RelativeTime::class, $dto->start_offset);
        $this->assertSame('discount', $dto->kind);
    }

    public function testValidation(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new TokenFamilyCreateRequest(
            slug: '',
            name: 'x',
            description: 'y',
            description_i18n: null,
            extra_data: null,
            valid_after: null,
            valid_before: new Timestamp(1700000000),
            duration: new RelativeTime(1000),
            validity_granularity: new RelativeTime(60000000),
            start_offset: new RelativeTime(0),
            kind: 'discount'
        );
    }
}



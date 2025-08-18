<?php

namespace Taler\Tests\Api\TokenFamilies\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\Timestamp;
use Taler\Api\TokenFamilies\Dto\TokenFamilyUpdateRequest;

class TokenFamilyUpdateRequestTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = [
            'name' => 'Updated Name',
            'description' => 'Updated Desc',
            'description_i18n' => ['en' => 'Updated'],
            'extra_data' => ['trusted_domains' => ['example.com']],
            'valid_after' => ['t_s' => 1700000000],
            'valid_before' => ['t_s' => 1800000000],
        ];

        $dto = TokenFamilyUpdateRequest::createFromArray($data);

        $this->assertSame('Updated Name', $dto->name);
        $this->assertSame('Updated Desc', $dto->description);
        $this->assertIsArray($dto->description_i18n);
        $this->assertIsArray($dto->extra_data);
        $this->assertInstanceOf(Timestamp::class, $dto->valid_after);
        $this->assertInstanceOf(Timestamp::class, $dto->valid_before);
    }

    public function testValidation(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new TokenFamilyUpdateRequest(
            name: '',
            description: 'x',
            description_i18n: null,
            extra_data: null,
            valid_after: new Timestamp(1),
            valid_before: new Timestamp(2)
        );
    }
}



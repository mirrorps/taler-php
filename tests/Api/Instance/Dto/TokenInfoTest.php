<?php

namespace Taler\Tests\Api\Instance\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Instance\Dto\TokenInfo;
use Taler\Api\Dto\Timestamp;

class TokenInfoTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = [
            'creation_time' => ['t_s' => 1],
            'expiration' => ['t_s' => 2],
            'scope' => 'readonly',
            'refreshable' => true,
            'description' => 'desc',
            'serial' => 10,
        ];

        $dto = TokenInfo::createFromArray($data);

        $this->assertInstanceOf(Timestamp::class, $dto->creation_time);
        $this->assertInstanceOf(Timestamp::class, $dto->expiration);
        $this->assertSame('readonly', $dto->scope);
        $this->assertTrue($dto->refreshable);
        $this->assertSame('desc', $dto->description);
        $this->assertSame(10, $dto->serial);
    }
}



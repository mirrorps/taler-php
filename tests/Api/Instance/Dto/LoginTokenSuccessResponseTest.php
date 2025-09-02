<?php

namespace Taler\Tests\Api\Instance\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Instance\Dto\LoginTokenSuccessResponse;
use Taler\Api\Dto\Timestamp;

class LoginTokenSuccessResponseTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = [
            'token' => 'deprecated-token',
            'access_token' => 'Bearer abc.def',
            'scope' => 'readonly',
            'expiration' => ['t_s' => 1700000000],
            'refreshable' => true,
        ];

        $dto = LoginTokenSuccessResponse::createFromArray($data);

        $this->assertSame('deprecated-token', $dto->token);
        $this->assertSame('Bearer abc.def', $dto->access_token);
        $this->assertSame('readonly', $dto->scope);
        $this->assertInstanceOf(Timestamp::class, $dto->expiration);
        $this->assertTrue($dto->refreshable);
    }
}



<?php

namespace Taler\Tests\Api\Instance\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Instance\Dto\TokenInfos;
use Taler\Api\Instance\Dto\TokenInfo;

class TokenInfosTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = [
            'tokens' => [
                [
                    'creation_time' => ['t_s' => 1],
                    'expiration' => ['t_s' => 2],
                    'scope' => 'readonly',
                    'refreshable' => false,
                    'serial' => 1,
                ],
                [
                    'creation_time' => ['t_s' => 3],
                    'expiration' => ['t_s' => 4],
                    'scope' => 'readwrite',
                    'refreshable' => true,
                    'description' => 'hello',
                    'serial' => 2,
                ],
            ],
        ];

        $dto = TokenInfos::createFromArray($data);

        $this->assertCount(2, $dto->tokens);
        $this->assertInstanceOf(TokenInfo::class, $dto->tokens[0]);
        $this->assertInstanceOf(TokenInfo::class, $dto->tokens[1]);
    }
}



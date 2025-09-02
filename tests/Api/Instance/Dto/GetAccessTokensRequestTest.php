<?php

namespace Taler\Tests\Api\Instance\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Instance\Dto\GetAccessTokensRequest;

class GetAccessTokensRequestTest extends TestCase
{
    public function testToArrayEmpty(): void
    {
        $dto = new GetAccessTokensRequest();
        $this->assertSame([], $dto->toArray());
    }

    public function testToArrayFull(): void
    {
        $dto = new GetAccessTokensRequest(limit: -20, offset: 100);
        $this->assertSame(['limit' => -20, 'offset' => 100], $dto->toArray());
    }

    public function testValidation(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new GetAccessTokensRequest(limit: 0);
    }
}



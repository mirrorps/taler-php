<?php

namespace Taler\Tests\Api\Instance\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Instance\Dto\LoginTokenRequest;
use Taler\Api\Dto\RelativeTime;

class LoginTokenRequestTest extends TestCase
{
    public function testValidConstructionMinimal(): void
    {
        $dto = new LoginTokenRequest('readonly');
        $this->assertSame('readonly', $dto->scope);
        $this->assertNull($dto->duration);
        $this->assertNull($dto->description);
        $this->assertNull($dto->refreshable);
    }

    public function testValidConstructionFull(): void
    {
        $dto = new LoginTokenRequest('order-full', new RelativeTime(1000), 'POS token', true);
        $this->assertSame('order-full', $dto->scope);
        $this->assertInstanceOf(RelativeTime::class, $dto->duration);
        $this->assertSame('POS token', $dto->description);
        $this->assertTrue($dto->refreshable);
    }

    public function testCreateFromArray(): void
    {
        $data = [
            'scope' => 'write',
            'duration' => ['d_us' => 10],
            'description' => 'desc',
            'refreshable' => false,
        ];
        $dto = LoginTokenRequest::createFromArray($data);
        $this->assertSame('write', $dto->scope);
        $this->assertInstanceOf(RelativeTime::class, $dto->duration);
        $this->assertSame('desc', $dto->description);
        $this->assertFalse($dto->refreshable);
    }

    public function testInvalidScope(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new LoginTokenRequest('invalid');
    }
}



<?php

namespace Taler\Tests\Api\Inventory\Dto;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\RelativeTime;
use Taler\Api\Inventory\Dto\LockRequest;

class LockRequestTest extends TestCase
{
    public function testValidConstruction(): void
    {
        $req = new LockRequest('123e4567-e89b-12d3-a456-426614174000', new RelativeTime(60_000_000), 2);
        $this->assertSame('123e4567-e89b-12d3-a456-426614174000', $req->lock_uuid);
        $this->assertSame(2, $req->quantity);
        $json = $req->jsonSerialize();
        $this->assertSame('123e4567-e89b-12d3-a456-426614174000', $json['lock_uuid']);
        $this->assertInstanceOf(RelativeTime::class, $json['duration']);
        $this->assertSame(2, $json['quantity']);
    }

    public function testCreateFromArray(): void
    {
        $req = LockRequest::createFromArray([
            'lock_uuid' => '123e4567-e89b-12d3-a456-426614174000',
            'duration' => ['d_us' => 1000000],
            'quantity' => 0,
        ]);
        $this->assertSame(0, $req->quantity);
    }

    public function testValidationFails(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new LockRequest('invalid-uuid', new RelativeTime(1), 1);
    }
}



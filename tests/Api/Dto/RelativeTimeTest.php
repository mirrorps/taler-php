<?php

namespace Taler\Tests\Api\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\RelativeTime;

class RelativeTimeTest extends TestCase
{
    public function testValidMicroseconds(): void
    {
        $duration = new RelativeTime(1000000); // 1 second
        $this->assertSame(1000000, $duration->d_us);
    }

    public function testForeverDuration(): void
    {
        $duration = new RelativeTime('forever');
        $this->assertSame('forever', $duration->d_us);
    }

    public function testFromArrayWithMicroseconds(): void
    {
        $duration = RelativeTime::createFromArray(['d_us' => 500000]);
        $this->assertSame(500000, $duration->d_us);
    }

    public function testFromArrayWithForever(): void
    {
        $duration = RelativeTime::createFromArray(['d_us' => 'forever']);
        $this->assertSame('forever', $duration->d_us);
    }

    public function testMaxMicroseconds(): void
    {
        $duration = new RelativeTime(9007199254740991);
        $this->assertSame(9007199254740991, $duration->d_us);
    }

    public function testInvalidNegativeMicroseconds(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new RelativeTime(-1);
    }

    public function testInvalidTooLargeMicroseconds(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new RelativeTime(9007199254740992);
    }

    public function testInvalidStringDuration(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new RelativeTime('invalid');
    }
} 
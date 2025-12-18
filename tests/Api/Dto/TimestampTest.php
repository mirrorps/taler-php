<?php

namespace Taler\Tests\Api\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\Timestamp;

class TimestampTest extends TestCase
{
    public function testValidTimestampSeconds(): void
    {
        $timestamp = new Timestamp(1710979200); // 2024-03-20T00:00:00Z
        $this->assertSame(1710979200, $timestamp->t_s);
    }

    public function testNeverTimestamp(): void
    {
        $timestamp = new Timestamp('never');
        $this->assertSame('never', $timestamp->t_s);
    }

    public function testZeroTimestamp(): void
    {
        $timestamp = new Timestamp(0); // Unix epoch
        $this->assertSame(0, $timestamp->t_s);
    }

    public function testFromArrayWithSeconds(): void
    {
        $timestamp = Timestamp::createFromArray(['t_s' => 1710979200]);
        $this->assertSame(1710979200, $timestamp->t_s);
    }

    public function testFromArrayWithNever(): void
    {
        $timestamp = Timestamp::createFromArray(['t_s' => 'never']);
        $this->assertSame('never', $timestamp->t_s);
    }

    public function testFromArrayWithZero(): void
    {
        $timestamp = Timestamp::createFromArray(['t_s' => 0]);
        $this->assertSame(0, $timestamp->t_s);
    }

    public function testInvalidNegativeTimestamp(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Timestamp must be non-negative');
        new Timestamp(-1);
    }

    public function testInvalidStringTimestamp(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('String timestamp can only be "never"');
        new Timestamp('invalid');
    }

    public function testInvalidEmptyString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('String timestamp can only be "never"');
        new Timestamp('');
    }

    public function testFromArrayMissingTsField(): void
    {
        $this->expectException(\TypeError::class);
        Timestamp::createFromArray([]); // @phpstan-ignore-line - Intentionally passing invalid data to test error handling
    }

    public function testFromArrayWithInvalidData(): void
    {
        $this->expectException(\TypeError::class);
        Timestamp::createFromArray(['invalid_key' => 123]); // @phpstan-ignore-line - Intentionally passing invalid data to test error handling
    }

    public function testLargeTimestamp(): void
    {
        $largeTimestamp = 2147483647; // Year 2038 problem timestamp
        $timestamp = new Timestamp($largeTimestamp);
        $this->assertSame($largeTimestamp, $timestamp->t_s);
    }

    public function testVeryLargeTimestamp(): void
    {
        $veryLargeTimestamp = 9007199254740991; // JavaScript's MAX_SAFE_INTEGER
        $timestamp = new Timestamp($veryLargeTimestamp);
        $this->assertSame($veryLargeTimestamp, $timestamp->t_s);
    }
} 
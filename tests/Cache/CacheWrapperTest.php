<?php

namespace Taler\Tests\Cache;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\SimpleCache\CacheInterface;
use Taler\Cache\CacheWrapper;

class CacheWrapperTest extends TestCase
{
    private CacheInterface&MockObject $cacheMock;
    private CacheWrapper $wrapper;

    protected function setUp(): void
    {
        $this->cacheMock = $this->createMock(CacheInterface::class);
        $this->wrapper = new CacheWrapper($this->cacheMock);
    }

    public function testConstructorAndGetCache(): void
    {
        $this->assertSame($this->cacheMock, $this->wrapper->getCache());
    }

    public function testTtlManagement(): void
    {
        // Initial state
        $this->assertNull($this->wrapper->getTtl());

        // Set TTL
        $ttl = 3600;
        $this->wrapper->setTtl($ttl);
        $this->assertSame($ttl, $this->wrapper->getTtl());

        // Clear settings
        $this->wrapper->clearCacheSettings();
        $this->assertNull($this->wrapper->getTtl());
    }

    public function testCacheKeyManagement(): void
    {
        // Initial state
        $this->assertNull($this->wrapper->getCacheKey());

        // Set cache key
        $key = 'test_cache_key';
        $this->wrapper->setCacheKey($key);
        $this->assertSame($key, $this->wrapper->getCacheKey());

        // Clear settings
        $this->wrapper->clearCacheSettings();
        $this->assertNull($this->wrapper->getCacheKey());
    }

    public function testClearCacheSettings(): void
    {
        // Set both TTL and cache key
        $this->wrapper->setTtl(3600);
        $this->wrapper->setCacheKey('test_key');

        // Verify they are set
        $this->assertNotNull($this->wrapper->getTtl());
        $this->assertNotNull($this->wrapper->getCacheKey());

        // Clear settings
        $this->wrapper->clearCacheSettings();

        // Verify both are cleared
        $this->assertNull($this->wrapper->getTtl());
        $this->assertNull($this->wrapper->getCacheKey());
    }
} 
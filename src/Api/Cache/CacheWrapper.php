<?php

namespace Taler\Api\Cache;

use Psr\SimpleCache\CacheInterface;

class CacheWrapper 
{
    protected ?int $ttl = null;
    protected ?string $cacheKey = null;

    public function __construct(
        public readonly CacheInterface $cache
    ) {}

    public function getCache(): CacheInterface
    {
        return $this->cache;
    }

    public function setTtl(int $ttl): void
    {
        $this->ttl = $ttl;
    }

    public function getTtl(): ?int
    {
        return $this->ttl;
    }

    public function setCacheKey(string $cacheKey): void
    {
        $this->cacheKey = $cacheKey;
    }

    public function getCacheKey(): ?string
    {
        return $this->cacheKey;
    }

    /**
     * Clear cache settings after API call
     */
    public function clearCacheSettings(): void
    {
        $this->ttl = null;
        $this->cacheKey = null;
    }
}
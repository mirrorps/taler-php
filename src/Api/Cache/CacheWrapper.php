<?php

namespace Taler\Api\Cache;

use Psr\SimpleCache\CacheInterface;

class CacheWrapper 
{
    protected ?int $ttl = null;

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

    /**
     * Clear cache settings after API call
     */
    public function clearCacheSettings(): void
    {
        $this->ttl = null;
    }
}
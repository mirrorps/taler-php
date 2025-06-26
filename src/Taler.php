<?php

namespace Taler;

use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Psr\SimpleCache\CacheInterface;
use Taler\Api\Exchange\ExchangeClient;
use Taler\Config\TalerConfig;
use Taler\Http\HttpClientWrapper;
use Taler\Api\Cache\CacheWrapper;

class Taler
{
    protected HttpClientWrapper $httpClientWrapper;
    protected ?CacheWrapper $cacheWrapper;
    protected ExchangeClient $exchange;

    /**
     * Taler constructor.
     * 
     * Creates a new instance of the Taler client with the given configuration and HTTP client.
     * 
     * @param TalerConfig $config The configuration for the Taler client
     * @param ClientInterface|null $client Optional PSR-18 HTTP client implementation
     * @param CacheInterface|null $cache Optional PSR-16 cache implementation
     */
    public function __construct(
        protected TalerConfig $config,
        protected ?ClientInterface $client = null,
        protected ?LoggerInterface $logger = null,
        protected ?CacheInterface $cache = null
    )
    {
        $this->httpClientWrapper = new HttpClientWrapper($config, $client, $logger);
        $this->cacheWrapper = $cache ? new CacheWrapper($cache) : null;
        $this->logger ??= new NullLogger();
    }

    /**
     * Get the HTTP client wrapper instance
     * 
     * @return HttpClientWrapper The configured HTTP client wrapper
     */
    public function getHttpClientWrapper(): HttpClientWrapper
    {
        return $this->httpClientWrapper;
    }

    public function getCacheWrapper(): ?CacheWrapper
    {
        return $this->cacheWrapper;
    }

    /**
     * Get the Taler configuration instance
     * 
     * @return TalerConfig The current configuration
     */
    public function getConfig(): TalerConfig
    {
        return $this->config;
    }

    /**
     * Get the logger instance
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Get the Exchange API client instance
     * 
     * Creates a new instance if one doesn't exist yet, otherwise returns the existing instance.
     * 
     * @return ExchangeClient The Exchange API client
     */
    public function exchange(): ExchangeClient
    {
        $this->exchange ??= new ExchangeClient(
            $this,
            $this->httpClientWrapper
        );

        return $this->exchange;
    }

    /**
     * Update configuration with new values
     * 
     * Allows fluent configuration updates by returning $this.
     * 
     * @param array<string, mixed> $config Array of configuration values to update
     * @return self Returns $this for method chaining
     * @throws \InvalidArgumentException When any of the configuration attributes do not exist
     */
    public function config(array $config): self
    {
        $this->getConfig()->setAttributes($config);
        return $this;
    }

    /**
     * Enable caching for the next API call with specified TTL in minutes
     *
     * @param int $minutes Time to live in minutes
     * @return static
     */
    public function cache(int $minutes, ?string $cacheKey = null): static
    {
        $cacheWrapper = $this->getCacheWrapper();

        if($cacheWrapper === null) {
            throw new \Exception('Cache is not set');
        }

        $cacheWrapper->setTtl($minutes * 60); // Convert to seconds
        
        if($cacheKey !== null) {
            $cacheWrapper->setCacheKey($cacheKey);
        }

        return $this;
    }

    public function cacheDelete(string $cacheKey): static
    {
        $cacheWrapper = $this->getCacheWrapper();

        if($cacheWrapper === null) {
            throw new \Exception('Cache is not set');
        }

        $cacheWrapper->getCache()->delete($cacheKey);

        return $this;
    }
}

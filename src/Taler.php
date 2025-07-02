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
use Taler\Api\Order\OrderClient;
use Taler\Api\Wallet\WalletClient;

class Taler
{
    protected HttpClientWrapper $httpClientWrapper;
    protected ?CacheWrapper $cacheWrapper;
    protected ExchangeClient $exchange;
    protected OrderClient $order;
    protected WalletClient $wallet;

    /**
     * Taler constructor.
     * 
     * Creates a new instance of the Taler client with the given configuration and HTTP client.
     * 
     * @param TalerConfig $config The configuration for the Taler client
     * @param ClientInterface|null $client Optional PSR-18 HTTP client implementation
     * @param LoggerInterface|null $logger Optional PSR-3 logger implementation
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
     * Get the Order API client instance
     * 
     * Creates a new instance if one doesn't exist yet, otherwise returns the existing instance.
     * 
     * @return OrderClient The Order API client
     */
    public function order(): OrderClient
    {
        $this->order ??= new OrderClient(
            $this,
            $this->httpClientWrapper
        );

        return $this->order;
    }

    /**
     * Get the Wallet API client instance
     * 
     * Creates a new instance if one doesn't exist yet, otherwise returns the existing instance.
     * 
     * @return WalletClient The Wallet API client
     */
    public function wallet(): WalletClient
    {
        $this->wallet ??= new WalletClient(
            $this,
            $this->httpClientWrapper
        );

        return $this->wallet;
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

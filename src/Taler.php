<?php

namespace Taler;

use Psr\Http\Client\ClientInterface;
use Taler\Api\Exchange\ExchangeClient;
use Taler\Config\TalerConfig;
use Taler\Http\HttpClientWrapper;

class Taler
{
    protected HttpClientWrapper $httpClientWrapper;
    protected ExchangeClient $exchange;

    /**
     * Taler constructor.
     * 
     * Creates a new instance of the Taler client with the given configuration and HTTP client.
     * 
     * @param TalerConfig $config The configuration for the Taler client
     * @param ClientInterface|null $client Optional PSR-18 HTTP client implementation
     */
    public function __construct(
        protected TalerConfig $config,
        protected ?ClientInterface $client = null
    )
    {
        $this->httpClientWrapper = new HttpClientWrapper($config, $client);
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
}

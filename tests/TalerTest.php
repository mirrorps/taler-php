<?php

namespace Taler\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Client\ClientInterface;
use Taler\Api\Exchange\ExchangeClient;
use Taler\Api\Order\OrderClient;
use Taler\Config\TalerConfig;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

class TalerTest extends TestCase
{
    private TalerConfig $config;
    /** @var ClientInterface&MockObject */
    private $httpClient;
    private Taler $taler;

    private const BASE_URL = 'https://api.taler.net';

    protected function setUp(): void
    {
        $this->config = new TalerConfig(self::BASE_URL);
        $this->config->setAttribute('wrapResponse', true);

        $this->httpClient = $this->createMock(ClientInterface::class);
        $this->taler = new Taler($this->config, $this->httpClient);
    }

    public function testConstructorWithConfig(): void
    {
        $this->assertInstanceOf(Taler::class, $this->taler);
        $this->assertSame($this->config, $this->taler->getConfig());
    }

    public function testConstructorWithoutHttpClient(): void
    {
        $taler = new Taler($this->config);
        $this->assertInstanceOf(Taler::class, $taler);
        $this->assertInstanceOf(HttpClientWrapper::class, $taler->getHttpClientWrapper());
    }

    public function testGetHttpClientWrapper(): void
    {
        $wrapper = $this->taler->getHttpClientWrapper();
        $this->assertInstanceOf(HttpClientWrapper::class, $wrapper);
    }

    public function testGetConfig(): void
    {
        $config = $this->taler->getConfig();
        $this->assertInstanceOf(TalerConfig::class, $config);
        $this->assertSame(self::BASE_URL, $config->getBaseUrl());
        $this->assertTrue($config->getWrapResponse());
    }

    public function testExchangeClientCreation(): void
    {
        $exchange = $this->taler->exchange();
        $this->assertInstanceOf(ExchangeClient::class, $exchange);
        
        // Test that the same instance is returned on subsequent calls
        $this->assertSame($exchange, $this->taler->exchange());
    }

    public function testConfigUpdate(): void
    {
        $this->taler->config([
            'wrapResponse' => false
        ]);

        $config = $this->taler->getConfig();
        $this->assertFalse($config->getWrapResponse());
    }

    public function testConfigUpdateWithInvalidAttribute(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $this->taler->config([
            'invalid_attribute' => 'value'
        ]);
    }

    public function testFluentConfigInterface(): void
    {
        $result = $this->taler->config([
            'wrapResponse' => false
        ]);

        $this->assertSame($this->taler, $result);
        $this->assertFalse($this->taler->getConfig()->getWrapResponse());
    }

    public function testOrderClientCreation(): void
    {
        $order = $this->taler->order();
        $this->assertInstanceOf(OrderClient::class, $order);
        
        // Test that the same instance is returned on subsequent calls
        $this->assertSame($order, $this->taler->order());
    }
} 
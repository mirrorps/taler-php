<?php

namespace Taler\Tests\Config;

use PHPUnit\Framework\TestCase;
use Taler\Config\TalerConfig;

class TalerConfigTest extends TestCase
{
    private const BASE_URL = 'https://api.taler.net';
    private const AUTH_TOKEN = 'Bearer test-token';

    private TalerConfig $config;

    protected function setUp(): void
    {
        $this->config = new TalerConfig(self::BASE_URL);
    }

    public function testConstructorWithDefaults(): void
    {
        $config = new TalerConfig(self::BASE_URL);
        
        $this->assertSame(self::BASE_URL, $config->getBaseUrl());
        $this->assertSame('', $config->getAuthToken());
        $this->assertTrue($config->getWrapResponse());
    }

    public function testConstructorWithAllParameters(): void
    {
        $config = new TalerConfig(
            baseUrl: self::BASE_URL,
            authToken: self::AUTH_TOKEN,
            wrapResponse: false
        );
        
        $this->assertSame(self::BASE_URL, $config->getBaseUrl());
        $this->assertSame(self::AUTH_TOKEN, $config->getAuthToken());
        $this->assertFalse($config->getWrapResponse());
    }

    public function testRequestCompressionDefaults(): void
    {
        $config = new TalerConfig(self::BASE_URL);
        $this->assertTrue($config->isRequestCompressionEnabled());
        $this->assertSame(4096, $config->getRequestCompressionThresholdBytes());
    }

    public function testRequestCompressionCustomConfig(): void
    {
        $config = new TalerConfig(
            baseUrl: self::BASE_URL,
            authToken: self::AUTH_TOKEN,
            wrapResponse: false,
            debugLoggingEnabled: false,
            requestCompressionEnabled: false,
            requestCompressionThresholdBytes: 1024
        );

        $this->assertFalse($config->isRequestCompressionEnabled());
        $this->assertSame(1024, $config->getRequestCompressionThresholdBytes());
    }

    public function testSetAttributeAllowsChangingCompressionSettings(): void
    {
        $config = new TalerConfig(self::BASE_URL);
        $config->setAttribute('requestCompressionEnabled', false);
        $config->setAttribute('requestCompressionThresholdBytes', 8192);

        $this->assertFalse($config->isRequestCompressionEnabled());
        $this->assertSame(8192, $config->getRequestCompressionThresholdBytes());
    }

    public function testConstructorThrowsExceptionOnEmptyBaseUrl(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required "base_url" in options.');
        
        new TalerConfig('');
    }

    public function testConstructorThrowsExceptionOnInvalidBaseUrl(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid base URL provided, only https schema is allowed');
        
        new TalerConfig('http://api.taler.net');
    }

    public function testSetSingleAttribute(): void
    {
        $this->config->setAttribute('authToken', self::AUTH_TOKEN);
        $this->assertSame(self::AUTH_TOKEN, $this->config->getAuthToken());

        $this->config->setAttribute('wrapResponse', false);
        $this->assertFalse($this->config->getWrapResponse());

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("The attribute 'baseUrl' cannot be modified after construction.");
        $this->config->setAttribute('baseUrl', self::BASE_URL . '/new');
    }

    public function testSetAttributeThrowsExceptionOnInvalidAttribute(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("The attribute 'invalidAttribute' does not exist.");
        
        $this->config->setAttribute('invalidAttribute', 'value');
    }

    public function testSetMultipleAttributes(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("The attribute 'baseUrl' cannot be modified after construction.");
        $this->config->setAttributes([
            'authToken' => self::AUTH_TOKEN,
            'wrapResponse' => false,
            'baseUrl' => self::BASE_URL . '/new'
        ]);
    }

    public function testSetAttributesThrowsExceptionOnInvalidAttribute(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("The attribute 'invalidAttribute' does not exist.");
        
        $this->config->setAttributes([
            'authToken' => self::AUTH_TOKEN,
            'invalidAttribute' => 'value'
        ]);
    }

    /**
     * @dataProvider invalidBaseUrlProvider
     */
    public function testInvalidBaseUrls(string $baseUrl): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new TalerConfig($baseUrl);
    }

    /**
     * @return array<string, array<string>>
     */
    public function invalidBaseUrlProvider(): array
    {
        return [
            'Empty URL' => [''],
            'HTTP URL' => ['http://api.taler.net'],
            'FTP URL' => ['ftp://api.taler.net'],
            'Invalid URL' => ['not-a-url'],
            'Missing scheme' => ['api.taler.net'],
            // 'Local URL' => ['https://localhost']
        ];
    }
} 
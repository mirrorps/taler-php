<?php

namespace Taler\Tests\Api\Exchange\Actions;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Taler\Api\Exchange\Actions\Config;
use Taler\Api\Exchange\ExchangeClient;
use Taler\Api\Exchange\Dto\ExchangeVersionResponse;
use Taler\Exception\TalerException;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Taler\Api\Cache\CacheWrapper;
use Taler\Config\TalerConfig;
use Taler\Taler;
use Taler\Http\HttpClientWrapper;
use Psr\Http\Message\StreamInterface;
use GuzzleHttp\Promise\Promise;

class ConfigTest extends TestCase
{
    private ExchangeClient $exchangeClient;
    private ResponseInterface&MockObject $response;
    private StreamInterface&MockObject $stream;
    private CacheInterface&MockObject $cache;
    private LoggerInterface&MockObject $logger;
    private Taler&MockObject $taler;
    private CacheWrapper&MockObject $cacheWrapper;
    private HttpClientWrapper&MockObject $httpClientWrapper;

    protected function setUp(): void
    {
        $this->response = $this->createMock(ResponseInterface::class);
        $this->stream = $this->createMock(StreamInterface::class);
        $this->cache = $this->createMock(CacheInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->taler = $this->createMock(Taler::class);
        $this->cacheWrapper = $this->createMock(CacheWrapper::class);
        
        $this->taler->method('getLogger')->willReturn($this->logger);
        $this->taler->method('getCacheWrapper')->willReturn($this->cacheWrapper);
        $this->taler->method('getConfig')->willReturn(new TalerConfig('https://example.com', '', true));
        
        // Create HttpClientWrapper mock with request and requestAsync methods
        $this->httpClientWrapper = $this->getMockBuilder(HttpClientWrapper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['request', 'requestAsync'])
            ->getMock();
        
        $this->exchangeClient = new ExchangeClient($this->taler, $this->httpClientWrapper);
    }

    public function testRunSuccess(): void
    {
        $expectedData = [
            'version' => '1.0.0',
            'name' => 'Test Exchange',
            'currency' => 'EUR',
            'currency_specification' => [
                'name' => 'Euro',
                'currency' => 'EUR',
                'num_fractional_input_digits' => 2,
                'num_fractional_normal_digits' => 2,
                'num_fractional_trailing_zero_digits' => 2,
                'alt_unit_names' => [
                    '0' => 'EUR'
                ]
            ],
            'supported_kyc_requirements' => [],
            'implementation' => 'test',
            'shopping_url' => 'https://example.com',
            'aml_spa_dialect' => 'test'
        ];

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')
            ->willReturn(json_encode($expectedData));
        $this->response->method('getBody')
            ->willReturn($this->stream);

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('GET', 'config', [])
            ->willReturn($this->response);

        $this->cacheWrapper->method('getTtl')->willReturn(null);

        $result = Config::run($this->exchangeClient);

        $this->assertInstanceOf(ExchangeVersionResponse::class, $result);
        $this->assertEquals($expectedData['version'], $result->version);
        $this->assertEquals($expectedData['name'], $result->name);
        $this->assertEquals($expectedData['currency'], $result->currency);
    }

    public function testRunWithCache(): void
    {
        $cachedData = ExchangeVersionResponse::fromArray([
            'version' => '1.0.0',
            'name' => 'Cached Exchange',
            'currency' => 'USD',
            'currency_specification' => [
                'name' => 'US Dollar',
                'currency' => 'USD',
                'num_fractional_input_digits' => 2,
                'num_fractional_normal_digits' => 2,
                'num_fractional_trailing_zero_digits' => 2,
                'alt_unit_names' => [
                    '0' => 'USD'
                ]
            ],
            'supported_kyc_requirements' => [],
            'implementation' => null,
            'shopping_url' => null,
            'aml_spa_dialect' => null
        ]);

        $this->cacheWrapper->method('getTtl')->willReturn(3600);
        $this->cacheWrapper->method('getCache')->willReturn($this->cache);
        $this->cacheWrapper->method('getCacheKey')->willReturn('test_cache_key');

        $this->cache->expects($this->once())
            ->method('get')
            ->with('test_cache_key')
            ->willReturn($cachedData);

        $result = Config::run($this->exchangeClient);

        $this->assertInstanceOf(ExchangeVersionResponse::class, $result);
        $this->assertEquals($cachedData->version, $result->version);
        $this->assertEquals($cachedData->name, $result->name);
        $this->assertEquals($cachedData->currency, $result->currency);
    }

    public function testRunWithException(): void
    {
        $this->expectException(TalerException::class);

        $this->httpClientWrapper->method('request')
            ->willThrowException(new TalerException('Test exception'));

        $this->cacheWrapper->method('getTtl')->willReturn(null);

        Config::run($this->exchangeClient);
    }

    public function testRunAsync(): void
    {
        $expectedData = [
            'version' => '1.0.0',
            'name' => 'Test Exchange',
            'currency' => 'EUR',
            'currency_specification' => [
                'name' => 'Euro',
                'currency' => 'EUR',
                'num_fractional_input_digits' => 2,
                'num_fractional_normal_digits' => 2,
                'num_fractional_trailing_zero_digits' => 2,
                'alt_unit_names' => [
                    '0' => 'EUR'
                ]
            ],
            'supported_kyc_requirements' => [],
            'implementation' => 'test',
            'shopping_url' => 'https://example.com',
            'aml_spa_dialect' => 'test'
        ];

        $promise = new Promise();
        
        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')
            ->willReturn(json_encode($expectedData));
        $this->response->method('getBody')
            ->willReturn($this->stream);

        $this->httpClientWrapper->expects($this->once())
            ->method('requestAsync')
            ->with('GET', 'config', [])
            ->willReturn($promise);

        $result = Config::runAsync($this->exchangeClient);
        $promise->resolve($this->response);

        $this->assertInstanceOf(ExchangeVersionResponse::class, $result->wait());
        $this->assertEquals($expectedData['version'], $result->wait()->version);
        $this->assertEquals($expectedData['name'], $result->wait()->name);
        $this->assertEquals($expectedData['currency'], $result->wait()->currency);
    }
} 
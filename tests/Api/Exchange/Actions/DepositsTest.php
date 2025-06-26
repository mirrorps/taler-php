<?php

namespace Taler\Tests\Api\Exchange\Actions;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\Promise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Taler\Api\Dto\Timestamp;
use Taler\Api\Exchange\Actions\Deposits;
use Taler\Api\Exchange\Dto\TrackTransactionResponse;
use Taler\Api\Exchange\Dto\TrackTransactionAcceptedResponse;
use Taler\Api\Exchange\ExchangeClient;
use Taler\Http\HttpClientWrapper;
use Taler\Config\TalerConfig;
use Taler\Taler;

class DepositsTest extends TestCase
{
    private const TEST_DATA = [
        'wtid' => 'test_wtid',
        'execution_time' => ['t_s' => 123456789],
        'coin_contribution' => '10.00',
        'exchange_sig' => 'test_exchange_sig',
        'exchange_pub' => 'test_exchange_pub'
    ];

    private const TEST_ACCEPTED_DATA = [
        'requirement_row' => 1,
        'kyc_ok' => false,
        'execution_time' => ['t_s' => 123456789],
        'account_pub' => 'test_account_pub'
    ];

    private const TEST_PARAMS = [
        'H_WIRE' => 'test_h_wire',
        'MERCHANT_PUB' => 'test_merchant_pub',
        'H_CONTRACT_TERMS' => 'test_h_contract_terms',
        'COIN_PUB' => 'test_coin_pub',
        'merchant_sig' => 'test_merchant_sig'
    ];

    public function testRunSuccess(): void
    {
        // Mock response and stream
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('__toString')->willReturn(json_encode(self::TEST_DATA));

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);
        $response->method('getStatusCode')->willReturn(200);

        // Mock HTTP client
        /** @var MockObject&HttpClientWrapper $httpClientWrapper */
        $httpClientWrapper = $this->createMock(HttpClientWrapper::class);
        $httpClientWrapper->method('request')->willReturn($response);

        // Mock Taler instance
        /** @var MockObject&Taler $taler */
        $taler = $this->createMock(Taler::class);
        $taler->method('getConfig')->willReturn(new TalerConfig('https://test.com'));
        $taler->method('getLogger')->willReturn($this->createMock(LoggerInterface::class));

        // Create ExchangeClient instance
        $exchangeClient = new ExchangeClient($taler, $httpClientWrapper);

        // Run test
        $result = Deposits::run(
            $exchangeClient,
            self::TEST_PARAMS['H_WIRE'],
            self::TEST_PARAMS['MERCHANT_PUB'],
            self::TEST_PARAMS['H_CONTRACT_TERMS'],
            self::TEST_PARAMS['COIN_PUB'],
            self::TEST_PARAMS['merchant_sig']
        );

        $this->assertInstanceOf(TrackTransactionResponse::class, $result);
        $this->assertEquals(self::TEST_DATA['wtid'], $result->wtid);
        $this->assertEquals(self::TEST_DATA['coin_contribution'], $result->coin_contribution);
    }

    public function testRunWithCache(): void
    {
        // Create cached response
        $cachedResponse = TrackTransactionResponse::fromArray(self::TEST_DATA);

        // Mock cache
        /** @var MockObject&CacheInterface $cache */
        $cache = $this->createMock(CacheInterface::class);
        $cache->expects($this->once())
            ->method('get')
            ->willReturn($cachedResponse);

        // Mock HTTP client (should not be called due to cache hit)
        /** @var MockObject&HttpClientWrapper $httpClientWrapper */
        $httpClientWrapper = $this->createMock(HttpClientWrapper::class);
        $httpClientWrapper->expects($this->never())->method('request');

        // Mock Taler instance with cache
        /** @var MockObject&Taler $taler */
        $taler = $this->createMock(Taler::class);
        $taler->method('getConfig')->willReturn(new TalerConfig('https://test.com'));
        $taler->method('getLogger')->willReturn($this->createMock(LoggerInterface::class));

        // Create cache wrapper
        $cacheWrapper = $this->createMock(\Taler\Api\Cache\CacheWrapper::class);
        $cacheWrapper->method('getCache')->willReturn($cache);
        $cacheWrapper->method('getTtl')->willReturn(3600);
        $cacheWrapper->method('getCacheKey')->willReturn('test_cache_key');

        $taler->method('getCacheWrapper')->willReturn($cacheWrapper);

        // Create ExchangeClient instance
        $exchangeClient = new ExchangeClient($taler, $httpClientWrapper);

        // Run test
        $result = Deposits::run(
            $exchangeClient,
            self::TEST_PARAMS['H_WIRE'],
            self::TEST_PARAMS['MERCHANT_PUB'],
            self::TEST_PARAMS['H_CONTRACT_TERMS'],
            self::TEST_PARAMS['COIN_PUB'],
            self::TEST_PARAMS['merchant_sig']
        );

        $this->assertInstanceOf(TrackTransactionResponse::class, $result);
        $this->assertEquals(self::TEST_DATA['wtid'], $result->wtid);
    }

    public function testRunAccepted(): void
    {
        // Mock response and stream
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('__toString')->willReturn(json_encode(self::TEST_ACCEPTED_DATA));

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);
        $response->method('getStatusCode')->willReturn(202);

        // Mock HTTP client
        /** @var MockObject&HttpClientWrapper $httpClientWrapper */
        $httpClientWrapper = $this->createMock(HttpClientWrapper::class);
        $httpClientWrapper->method('request')->willReturn($response);

        // Mock Taler instance
        /** @var MockObject&Taler $taler */
        $taler = $this->createMock(Taler::class);
        $taler->method('getConfig')->willReturn(new TalerConfig('https://test.com'));
        $taler->method('getLogger')->willReturn($this->createMock(LoggerInterface::class));

        // Create ExchangeClient instance
        $exchangeClient = new ExchangeClient($taler, $httpClientWrapper);

        // Run test
        $result = Deposits::run(
            $exchangeClient,
            self::TEST_PARAMS['H_WIRE'],
            self::TEST_PARAMS['MERCHANT_PUB'],
            self::TEST_PARAMS['H_CONTRACT_TERMS'],
            self::TEST_PARAMS['COIN_PUB'],
            self::TEST_PARAMS['merchant_sig']
        );

        $this->assertInstanceOf(TrackTransactionAcceptedResponse::class, $result);
        $this->assertEquals(self::TEST_ACCEPTED_DATA['requirement_row'], $result->requirement_row);
        $this->assertEquals(self::TEST_ACCEPTED_DATA['kyc_ok'], $result->kyc_ok);
    }

    public function testRunAsync(): void
    {
        // Create a promise that will resolve with our test response
        $promise = new Promise(function () use (&$promise) {
            // Mock response and stream
            $stream = $this->createMock(StreamInterface::class);
            $stream->method('__toString')->willReturn(json_encode(self::TEST_DATA));

            $response = $this->createMock(ResponseInterface::class);
            $response->method('getBody')->willReturn($stream);
            $response->method('getStatusCode')->willReturn(200);

            $promise->resolve($response);
        });

        // Mock HTTP client
        /** @var MockObject&HttpClientWrapper $httpClientWrapper */
        $httpClientWrapper = $this->createMock(HttpClientWrapper::class);
        $httpClientWrapper->method('requestAsync')->willReturn($promise);

        // Mock Taler instance
        /** @var MockObject&Taler $taler */
        $taler = $this->createMock(Taler::class);
        $taler->method('getConfig')->willReturn(new TalerConfig('https://test.com'));
        $taler->method('getLogger')->willReturn($this->createMock(LoggerInterface::class));

        // Create ExchangeClient instance
        $exchangeClient = new ExchangeClient($taler, $httpClientWrapper);

        // Run test
        $promise = Deposits::runAsync(
            $exchangeClient,
            self::TEST_PARAMS['H_WIRE'],
            self::TEST_PARAMS['MERCHANT_PUB'],
            self::TEST_PARAMS['H_CONTRACT_TERMS'],
            self::TEST_PARAMS['COIN_PUB'],
            self::TEST_PARAMS['merchant_sig']
        );

        // Wait for the promise to resolve and get the response
        $response = $promise->wait();
        $this->assertInstanceOf(ResponseInterface::class, $response);

        // Parse the response body and create the DTO
        $data = json_decode((string)$response->getBody(), true);
        $result = TrackTransactionResponse::fromArray($data);

        $this->assertInstanceOf(TrackTransactionResponse::class, $result);
        $this->assertEquals(self::TEST_DATA['wtid'], $result->wtid);
        $this->assertEquals(self::TEST_DATA['coin_contribution'], $result->coin_contribution);
    }
} 
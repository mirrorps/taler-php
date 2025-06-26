<?php

namespace Taler\Tests\Api\Exchange\Actions;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Taler\Api\Exchange\Actions\Keys;
use Taler\Api\Exchange\ExchangeClient;
use Taler\Api\Exchange\Dto\ExchangeKeysResponse;
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

class KeysTest extends TestCase
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
            'base_url' => 'https://exchange.test',
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
            'stefan_abs' => '0',
            'stefan_log' => '0',
            'stefan_lin' => 0.0,
            'asset_type' => 'fiat',
            'accounts' => [],
            'wire_fees' => [],
            'wads' => [],
            'rewards_allowed' => false,
            'kyc_enabled' => false,
            'master_public_key' => 'test_master_key',
            'reserve_closing_delay' => ['d_us' => 3600000000],
            'wallet_balance_limit_without_kyc' => [],
            'hard_limits' => [],
            'zero_limits' => [],
            'denominations' => [
                [
                    'value' => '10.00',
                    'fee_withdraw' => '1.00',
                    'fee_deposit' => '0.50',
                    'fee_refresh' => '0.25',
                    'fee_refund' => '0.75',
                    'cipher' => 'RSA',
                    'denoms' => [
                        [
                            'master_sig' => 'test_master_sig',
                            'stamp_start' => ['t_s' => 123456789],
                            'stamp_expire_withdraw' => ['t_s' => 123456789],
                            'stamp_expire_deposit' => ['t_s' => 123456789],
                            'stamp_expire_legal' => ['t_s' => 123456789],
                            'rsa_pub' => 'test_rsa_pub'
                        ]
                    ]
                ]
            ],
            'exchange_sig' => 'test_exchange_sig',
            'exchange_pub' => 'test_exchange_pub',
            'recoup' => [],
            'global_fees' => [],
            'list_issue_date' => ['t_s' => 123456789],
            'auditors' => [],
            'signkeys' => []
        ];

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')
            ->willReturn(json_encode($expectedData));
        $this->response->method('getBody')
            ->willReturn($this->stream);

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('GET', 'keys?', [])
            ->willReturn($this->response);

        $this->cacheWrapper->method('getTtl')->willReturn(null);

        $result = Keys::run($this->exchangeClient);

        $this->assertInstanceOf(ExchangeKeysResponse::class, $result);
        $this->assertEquals($expectedData['master_public_key'], $result->master_public_key);
        $this->assertEquals($expectedData['exchange_pub'], $result->exchange_pub);
        $this->assertEquals($expectedData['exchange_sig'], $result->exchange_sig);
        $this->assertEquals($expectedData['denominations'][0]['value'], $result->denominations[0]->getValue());
    }

    public function testRunWithCache(): void
    {
        $cachedData = [
            'version' => '1.0.0',
            'base_url' => 'https://exchange.test',
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
            'stefan_abs' => '0',
            'stefan_log' => '0',
            'stefan_lin' => 0.0,
            'asset_type' => 'fiat',
            'accounts' => [],
            'wire_fees' => [],
            'wads' => [],
            'rewards_allowed' => false,
            'kyc_enabled' => false,
            'master_public_key' => 'cached_master_key',
            'reserve_closing_delay' => ['d_us' => 3600000000],
            'wallet_balance_limit_without_kyc' => [],
            'hard_limits' => [],
            'zero_limits' => [],
            'denominations' => [
                [
                    'value' => '20.00',
                    'fee_withdraw' => '2.00',
                    'fee_deposit' => '1.00',
                    'fee_refresh' => '0.50',
                    'fee_refund' => '1.50',
                    'cipher' => 'RSA',
                    'denoms' => [
                        [
                            'master_sig' => 'cached_master_sig',
                            'stamp_start' => ['t_s' => 123456789],
                            'stamp_expire_withdraw' => ['t_s' => 123456789],
                            'stamp_expire_deposit' => ['t_s' => 123456789],
                            'stamp_expire_legal' => ['t_s' => 123456789],
                            'rsa_pub' => 'cached_rsa_pub'
                        ]
                    ]
                ]
            ],
            'exchange_sig' => 'cached_exchange_sig',
            'exchange_pub' => 'cached_exchange_pub',
            'recoup' => [],
            'global_fees' => [],
            'list_issue_date' => ['t_s' => 123456789],
            'auditors' => [],
            'signkeys' => []
        ];

        $this->cacheWrapper->method('getTtl')->willReturn(3600);
        $this->cacheWrapper->method('getCache')->willReturn($this->cache);
        $this->cacheWrapper->method('getCacheKey')->willReturn('test_cache_key');

        $this->cache->expects($this->once())
            ->method('get')
            ->with('test_cache_key')
            ->willReturn(ExchangeKeysResponse::fromArray($cachedData));

        $result = Keys::run($this->exchangeClient);

        $this->assertInstanceOf(ExchangeKeysResponse::class, $result);
        $this->assertEquals($cachedData['master_public_key'], $result->master_public_key);
        $this->assertEquals($cachedData['exchange_pub'], $result->exchange_pub);
        $this->assertEquals($cachedData['denominations'][0]['value'], $result->denominations[0]->getValue());
    }

    public function testRunWithException(): void
    {
        $this->expectException(TalerException::class);

        $this->httpClientWrapper->method('request')
            ->willThrowException(new TalerException('Test exception'));

        $this->cacheWrapper->method('getTtl')->willReturn(null);

        Keys::run($this->exchangeClient);
    }

    public function testRunAsync(): void
    {
        $expectedData = [
            'version' => '1.0.0',
            'base_url' => 'https://exchange.test',
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
            'stefan_abs' => '0',
            'stefan_log' => '0',
            'stefan_lin' => 0.0,
            'asset_type' => 'fiat',
            'accounts' => [],
            'wire_fees' => [],
            'wads' => [],
            'rewards_allowed' => false,
            'kyc_enabled' => false,
            'master_public_key' => 'test_master_key',
            'reserve_closing_delay' => ['d_us' => 3600000000],
            'wallet_balance_limit_without_kyc' => [],
            'hard_limits' => [],
            'zero_limits' => [],
            'denominations' => [
                [
                    'value' => '10.00',
                    'fee_withdraw' => '1.00',
                    'fee_deposit' => '0.50',
                    'fee_refresh' => '0.25',
                    'fee_refund' => '0.75',
                    'cipher' => 'RSA',
                    'denoms' => [
                        [
                            'master_sig' => 'test_master_sig',
                            'stamp_start' => ['t_s' => 123456789],
                            'stamp_expire_withdraw' => ['t_s' => 123456789],
                            'stamp_expire_deposit' => ['t_s' => 123456789],
                            'stamp_expire_legal' => ['t_s' => 123456789],
                            'rsa_pub' => 'test_rsa_pub'
                        ]
                    ]
                ]
            ],
            'exchange_sig' => 'test_exchange_sig',
            'exchange_pub' => 'test_exchange_pub',
            'recoup' => [],
            'global_fees' => [],
            'list_issue_date' => ['t_s' => 123456789],
            'auditors' => [],
            'signkeys' => []
        ];

        $promise = new Promise();
        
        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')
            ->willReturn(json_encode($expectedData));
        $this->response->method('getBody')
            ->willReturn($this->stream);

        $this->httpClientWrapper->expects($this->once())
            ->method('requestAsync')
            ->with('GET', 'keys?', [])
            ->willReturn($promise);

        $result = Keys::runAsync($this->exchangeClient);
        $promise->resolve($this->response);

        $this->assertInstanceOf(ExchangeKeysResponse::class, $result->wait());
        $this->assertEquals($expectedData['master_public_key'], $result->wait()->master_public_key);
        $this->assertEquals($expectedData['exchange_pub'], $result->wait()->exchange_pub);
        $this->assertEquals($expectedData['exchange_sig'], $result->wait()->exchange_sig);
    }
} 
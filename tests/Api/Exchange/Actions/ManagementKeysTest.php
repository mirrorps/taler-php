<?php

namespace Taler\Tests\Api\Exchange\Actions;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Taler\Api\Exchange\Actions\ManagementKeys;
use Taler\Api\Exchange\ExchangeClient;
use Taler\Api\Dto\FutureKeysResponse;
use Taler\Exception\TalerException;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Taler\Cache\CacheWrapper;
use Taler\Config\TalerConfig;
use Taler\Taler;
use Taler\Http\HttpClientWrapper;
use Psr\Http\Message\StreamInterface;
use GuzzleHttp\Promise\Promise;

class ManagementKeysTest extends TestCase
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
            'future_denoms' => [
                [
                    'section_name' => 'denom1',
                    'value' => 'TALER:10.00',
                    'stamp_start' => '2024-03-20',
                    'stamp_expire_withdraw' => '2024-06-20',
                    'stamp_expire_deposit' => '2024-09-20',
                    'stamp_expire_legal' => '2024-12-20',
                    'denom_pub' => 'test_denom_pub_1',
                    'fee_withdraw' => 'TALER:0.50',
                    'fee_deposit' => 'TALER:0.25',
                    'fee_refresh' => 'TALER:0.10',
                    'fee_refund' => 'TALER:0.15',
                    'denom_secmod_sig' => 'test_denom_sig_1'
                ]
            ],
            'future_signkeys' => [
                [
                    'key' => 'test_key_1',
                    'stamp_start' => '2024-03-20',
                    'stamp_expire' => '2024-06-20',
                    'stamp_expire_legal' => '2024-12-20',
                    'key_secmod_sig' => 'test_key_sig_1'
                ]
            ],
            'master_pub' => 'test_master_pub',
            'denom_secmod_public_key' => 'test_denom_secmod_pub',
            'signkey_secmod_public_key' => 'test_signkey_secmod_pub'
        ];

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')
            ->willReturn(json_encode($expectedData));
        $this->response->method('getBody')
            ->willReturn($this->stream);

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('GET', 'management/keys', [])
            ->willReturn($this->response);

        $this->cacheWrapper->method('getTtl')->willReturn(null);

        $result = ManagementKeys::run($this->exchangeClient);

        $this->assertInstanceOf(FutureKeysResponse::class, $result);
        $this->assertEquals($expectedData['master_pub'], $result->getMasterPub());
        $this->assertEquals($expectedData['denom_secmod_public_key'], $result->getDenomSecmodPublicKey());
        $this->assertEquals($expectedData['signkey_secmod_public_key'], $result->getSignkeySecmodPublicKey());
        $this->assertEquals($expectedData['future_denoms'][0]['section_name'], $result->getFutureDenoms()[0]->getSectionName());
        $this->assertEquals($expectedData['future_signkeys'][0]['key'], $result->getFutureSignkeys()[0]->key);
    }

    public function testRunWithCache(): void
    {
        $cachedData = [
            'future_denoms' => [
                [
                    'section_name' => 'cached_denom1',
                    'value' => 'TALER:20.00',
                    'stamp_start' => '2024-03-20',
                    'stamp_expire_withdraw' => '2024-06-20',
                    'stamp_expire_deposit' => '2024-09-20',
                    'stamp_expire_legal' => '2024-12-20',
                    'denom_pub' => 'cached_denom_pub_1',
                    'fee_withdraw' => 'TALER:1.00',
                    'fee_deposit' => 'TALER:0.50',
                    'fee_refresh' => 'TALER:0.20',
                    'fee_refund' => 'TALER:0.30',
                    'denom_secmod_sig' => 'cached_denom_sig_1'
                ]
            ],
            'future_signkeys' => [
                [
                    'key' => 'cached_key_1',
                    'stamp_start' => '2024-03-20',
                    'stamp_expire' => '2024-06-20',
                    'stamp_expire_legal' => '2024-12-20',
                    'key_secmod_sig' => 'cached_key_sig_1'
                ]
            ],
            'master_pub' => 'cached_master_pub',
            'denom_secmod_public_key' => 'cached_denom_secmod_pub',
            'signkey_secmod_public_key' => 'cached_signkey_secmod_pub'
        ];

        $this->cacheWrapper->method('getTtl')->willReturn(3600);
        $this->cacheWrapper->method('getCache')->willReturn($this->cache);
        $this->cacheWrapper->method('getCacheKey')->willReturn('test_cache_key');

        $this->cache->expects($this->once())
            ->method('get')
            ->with('test_cache_key')
            ->willReturn(FutureKeysResponse::createFromArray($cachedData));

        $result = ManagementKeys::run($this->exchangeClient);

        $this->assertInstanceOf(FutureKeysResponse::class, $result);
        $this->assertEquals($cachedData['master_pub'], $result->getMasterPub());
        $this->assertEquals($cachedData['denom_secmod_public_key'], $result->getDenomSecmodPublicKey());
        $this->assertEquals($cachedData['signkey_secmod_public_key'], $result->getSignkeySecmodPublicKey());
        $this->assertEquals($cachedData['future_denoms'][0]['section_name'], $result->getFutureDenoms()[0]->getSectionName());
        $this->assertEquals($cachedData['future_signkeys'][0]['key'], $result->getFutureSignkeys()[0]->key);
    }

    public function testRunWithException(): void
    {
        $this->expectException(TalerException::class);

        $this->httpClientWrapper->method('request')
            ->willThrowException(new TalerException('Test exception'));

        $this->cacheWrapper->method('getTtl')->willReturn(null);

        ManagementKeys::run($this->exchangeClient);
    }

    public function testRunAsync(): void
    {
        $expectedData = [
            'future_denoms' => [
                [
                    'section_name' => 'denom1',
                    'value' => 'TALER:10.00',
                    'stamp_start' => '2024-03-20',
                    'stamp_expire_withdraw' => '2024-06-20',
                    'stamp_expire_deposit' => '2024-09-20',
                    'stamp_expire_legal' => '2024-12-20',
                    'denom_pub' => 'test_denom_pub_1',
                    'fee_withdraw' => 'TALER:0.50',
                    'fee_deposit' => 'TALER:0.25',
                    'fee_refresh' => 'TALER:0.10',
                    'fee_refund' => 'TALER:0.15',
                    'denom_secmod_sig' => 'test_denom_sig_1'
                ]
            ],
            'future_signkeys' => [
                [
                    'key' => 'test_key_1',
                    'stamp_start' => '2024-03-20',
                    'stamp_expire' => '2024-06-20',
                    'stamp_expire_legal' => '2024-12-20',
                    'key_secmod_sig' => 'test_key_sig_1'
                ]
            ],
            'master_pub' => 'test_master_pub',
            'denom_secmod_public_key' => 'test_denom_secmod_pub',
            'signkey_secmod_public_key' => 'test_signkey_secmod_pub'
        ];

        $promise = new Promise();
        
        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')
            ->willReturn(json_encode($expectedData));
        $this->response->method('getBody')
            ->willReturn($this->stream);

        $this->httpClientWrapper->expects($this->once())
            ->method('requestAsync')
            ->with('GET', 'management/keys', [])
            ->willReturn($promise);

        $result = ManagementKeys::runAsync($this->exchangeClient);
        $promise->resolve($this->response);

        $this->assertInstanceOf(FutureKeysResponse::class, $result->wait());
        $this->assertEquals($expectedData['master_pub'], $result->wait()->getMasterPub());
        $this->assertEquals($expectedData['denom_secmod_public_key'], $result->wait()->getDenomSecmodPublicKey());
        $this->assertEquals($expectedData['signkey_secmod_public_key'], $result->wait()->getSignkeySecmodPublicKey());
    }
} 
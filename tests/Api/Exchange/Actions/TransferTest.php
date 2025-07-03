<?php

namespace Taler\Tests\Api\Exchange\Actions;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Taler\Api\Exchange\Actions\Transfer;
use Taler\Api\Exchange\ExchangeClient;
use Taler\Api\Exchange\Dto\TrackTransferResponse;
use Taler\Api\Exchange\Dto\TrackTransferDetail;
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

class TransferTest extends TestCase
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
            'total' => 'TALER:10.00',
            'wire_fee' => 'TALER:0.50',
            'merchant_pub' => 'test_merchant_pub',
            'h_payto' => 'test_h_payto',
            'execution_time' => ['t_s' => 1710510600],
            'deposits' => [
                [
                    'h_contract_terms' => 'test_contract_terms',
                    'coin_pub' => 'test_coin_pub',
                    'deposit_value' => 'TALER:9.00',
                    'deposit_fee' => 'TALER:0.50',
                    'refund_total' => 'TALER:0.00'
                ]
            ],
            'exchange_sig' => 'test_exchange_sig',
            'exchange_pub' => 'test_exchange_pub'
        ];

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')
            ->willReturn(json_encode($expectedData));
        $this->response->method('getBody')
            ->willReturn($this->stream);

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('GET', 'transfers/test_wtid', [])
            ->willReturn($this->response);

        $this->cacheWrapper->method('getTtl')->willReturn(null);

        $result = Transfer::run($this->exchangeClient, 'test_wtid');

        $this->assertInstanceOf(TrackTransferResponse::class, $result);
        $this->assertSame($expectedData['total'], $result->total);
        $this->assertSame($expectedData['wire_fee'], $result->wire_fee);
        $this->assertSame($expectedData['merchant_pub'], $result->merchant_pub);
        $this->assertSame($expectedData['h_payto'], $result->h_payto);
        $this->assertSame($expectedData['execution_time']['t_s'], $result->execution_time->t_s);
        $this->assertInstanceOf(TrackTransferDetail::class, $result->deposits[0]);
        $this->assertSame($expectedData['deposits'][0]['h_contract_terms'], $result->deposits[0]->h_contract_terms);
        $this->assertSame($expectedData['deposits'][0]['coin_pub'], $result->deposits[0]->coin_pub);
        $this->assertSame($expectedData['deposits'][0]['deposit_value'], $result->deposits[0]->deposit_value);
        $this->assertSame($expectedData['deposits'][0]['deposit_fee'], $result->deposits[0]->deposit_fee);
        $this->assertSame($expectedData['deposits'][0]['refund_total'], $result->deposits[0]->refund_total);
        $this->assertSame($expectedData['exchange_sig'], $result->exchange_sig);
        $this->assertSame($expectedData['exchange_pub'], $result->exchange_pub);
    }

    public function testRunWithCache(): void
    {
        $cachedData = [
            'total' => 'TALER:20.00',
            'wire_fee' => 'TALER:1.00',
            'merchant_pub' => 'cached_merchant_pub',
            'h_payto' => 'cached_h_payto',
            'execution_time' => ['t_s' => 1710510600],
            'deposits' => [
                [
                    'h_contract_terms' => 'cached_contract_terms',
                    'coin_pub' => 'cached_coin_pub',
                    'deposit_value' => 'TALER:18.00',
                    'deposit_fee' => 'TALER:1.00',
                    'refund_total' => 'TALER:0.00'
                ]
            ],
            'exchange_sig' => 'cached_exchange_sig',
            'exchange_pub' => 'cached_exchange_pub'
        ];

        $this->cacheWrapper->method('getTtl')->willReturn(3600);
        $this->cacheWrapper->method('getCache')->willReturn($this->cache);
        $this->cacheWrapper->method('getCacheKey')->willReturn('test_cache_key');

        $this->cache->expects($this->once())
            ->method('get')
            ->with('test_cache_key')
            ->willReturn(TrackTransferResponse::fromArray($cachedData));

        $result = Transfer::run($this->exchangeClient, 'test_wtid');

        $this->assertInstanceOf(TrackTransferResponse::class, $result);
        $this->assertSame($cachedData['total'], $result->total);
        $this->assertSame($cachedData['wire_fee'], $result->wire_fee);
        $this->assertSame($cachedData['merchant_pub'], $result->merchant_pub);
        $this->assertSame($cachedData['h_payto'], $result->h_payto);
        $this->assertSame($cachedData['execution_time']['t_s'], $result->execution_time->t_s);
        $this->assertInstanceOf(TrackTransferDetail::class, $result->deposits[0]);
        $this->assertSame($cachedData['deposits'][0]['h_contract_terms'], $result->deposits[0]->h_contract_terms);
        $this->assertSame($cachedData['deposits'][0]['coin_pub'], $result->deposits[0]->coin_pub);
        $this->assertSame($cachedData['deposits'][0]['deposit_value'], $result->deposits[0]->deposit_value);
        $this->assertSame($cachedData['deposits'][0]['deposit_fee'], $result->deposits[0]->deposit_fee);
        $this->assertSame($cachedData['deposits'][0]['refund_total'], $result->deposits[0]->refund_total);
        $this->assertSame($cachedData['exchange_sig'], $result->exchange_sig);
        $this->assertSame($cachedData['exchange_pub'], $result->exchange_pub);
    }

    public function testRunWithException(): void
    {
        $this->expectException(TalerException::class);

        $this->httpClientWrapper->method('request')
            ->willThrowException(new TalerException('Test exception'));

        $this->cacheWrapper->method('getTtl')->willReturn(null);

        Transfer::run($this->exchangeClient, 'test_wtid');
    }

    public function testRunAsync(): void
    {
        $expectedData = [
            'total' => 'TALER:10.00',
            'wire_fee' => 'TALER:0.50',
            'merchant_pub' => 'test_merchant_pub',
            'h_payto' => 'test_h_payto',
            'execution_time' => ['t_s' => 1710510600],
            'deposits' => [
                [
                    'h_contract_terms' => 'test_contract_terms',
                    'coin_pub' => 'test_coin_pub',
                    'deposit_value' => 'TALER:9.00',
                    'deposit_fee' => 'TALER:0.50',
                    'refund_total' => 'TALER:0.00'
                ]
            ],
            'exchange_sig' => 'test_exchange_sig',
            'exchange_pub' => 'test_exchange_pub'
        ];

        $promise = new Promise();
        
        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')
            ->willReturn(json_encode($expectedData));
        $this->response->method('getBody')
            ->willReturn($this->stream);

        $this->httpClientWrapper->expects($this->once())
            ->method('requestAsync')
            ->with('GET', 'transfers/test_wtid', [])
            ->willReturn($promise);

        $result = Transfer::runAsync($this->exchangeClient, 'test_wtid');
        $promise->resolve($this->response);

        $this->assertInstanceOf(TrackTransferResponse::class, $result->wait());
        $this->assertSame($expectedData['total'], $result->wait()->total);
        $this->assertSame($expectedData['wire_fee'], $result->wait()->wire_fee);
        $this->assertSame($expectedData['merchant_pub'], $result->wait()->merchant_pub);
        $this->assertSame($expectedData['h_payto'], $result->wait()->h_payto);
        $this->assertSame($expectedData['execution_time']['t_s'], $result->wait()->execution_time->t_s);
        $this->assertInstanceOf(TrackTransferDetail::class, $result->wait()->deposits[0]);
        $this->assertSame($expectedData['deposits'][0]['h_contract_terms'], $result->wait()->deposits[0]->h_contract_terms);
        $this->assertSame($expectedData['deposits'][0]['coin_pub'], $result->wait()->deposits[0]->coin_pub);
        $this->assertSame($expectedData['deposits'][0]['deposit_value'], $result->wait()->deposits[0]->deposit_value);
        $this->assertSame($expectedData['deposits'][0]['deposit_fee'], $result->wait()->deposits[0]->deposit_fee);
        $this->assertSame($expectedData['deposits'][0]['refund_total'], $result->wait()->deposits[0]->refund_total);
        $this->assertSame($expectedData['exchange_sig'], $result->wait()->exchange_sig);
        $this->assertSame($expectedData['exchange_pub'], $result->wait()->exchange_pub);
    }
} 
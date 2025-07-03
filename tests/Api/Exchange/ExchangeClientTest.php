<?php

namespace Taler\Tests\Api\Exchange;

use Http\Client\HttpAsyncClient;
use Http\Promise\Promise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\SimpleCache\CacheInterface;
use Taler\Api\Dto\ErrorDetail;
use Taler\Api\Dto\FutureKeysResponse;
use Taler\Api\Exchange\Dto\ExchangeKeysResponse;
use Taler\Api\Exchange\Dto\ExchangeVersionResponse;
use Taler\Api\Exchange\Dto\TrackTransactionAcceptedResponse;
use Taler\Api\Exchange\Dto\TrackTransactionResponse;
use Taler\Api\Exchange\Dto\TrackTransferResponse;
use Taler\Api\Exchange\ExchangeClient;
use Taler\Cache\CacheWrapper;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

class ExchangeClientTest extends TestCase
{
    private ExchangeClient $client;
    private HttpClientWrapper&MockObject $httpClient;
    private Taler&MockObject $taler;
    private ResponseInterface&MockObject $response;
    private StreamInterface&MockObject $stream;
    private Promise&MockObject $promise;
    private TalerConfig&MockObject $config;
    private CacheWrapper&MockObject $cacheWrapper;
    private CacheInterface&MockObject $cache;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientWrapper::class);
        $this->taler = $this->createMock(Taler::class);
        $this->response = $this->createMock(ResponseInterface::class);
        $this->stream = $this->createMock(StreamInterface::class);
        $this->promise = $this->createMock(Promise::class);
        $this->config = $this->createMock(TalerConfig::class);
        $this->cacheWrapper = $this->createMock(CacheWrapper::class);
        $this->cache = $this->createMock(CacheInterface::class);
        
        $this->taler->method('getConfig')->willReturn($this->config);
        $this->taler->method('getCacheWrapper')->willReturn($this->cacheWrapper);
        $this->cacheWrapper->method('getTtl')->willReturn(null);
        $this->cacheWrapper->method('getCache')->willReturn($this->cache);
        $this->config->method('getWrapResponse')->willReturn(true);
        
        $this->promise->method('then')->willReturnSelf();
        
        $this->client = new ExchangeClient($this->taler, $this->httpClient);
    }

    public function testGetConfig(): void
    {
        $expectedData = [
            'version' => '1.0.0',
            'name' => 'taler-exchange',
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
            'supported_kyc_requirements' => []
        ];
        $this->setupMockResponse($expectedData);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'config', [])
            ->willReturn($this->response);

        $result = $this->client->getConfig();
        $this->assertInstanceOf(ExchangeVersionResponse::class, $result);
        $this->assertEquals('1.0.0', $result->version);
        $this->assertEquals('EUR', $result->currency);
    }

    public function testGetConfigAsync(): void
    {
        $expectedData = ['some' => 'config'];
        $this->setupMockAsyncResponse($expectedData);

        $this->httpClient->expects($this->once())
            ->method('requestAsync')
            ->with('GET', 'config', [])
            ->willReturn($this->promise);

        $promise = $this->client->getConfigAsync();
        $this->assertInstanceOf(Promise::class, $promise);
        
        $result = $promise->wait();
        $this->assertEquals($expectedData, json_decode((string)$result->getBody(), true));
    }

    public function testGetKeys(): void
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
            'master_public_key' => 'test-master-key',
            'reserve_closing_delay' => ['d_us' => 3600000000],
            'wallet_balance_limit_without_kyc' => [],
            'hard_limits' => [],
            'zero_limits' => [],
            'denominations' => [],
            'exchange_sig' => 'test-sig',
            'exchange_pub' => 'test-pub',
            'recoup' => [],
            'global_fees' => [],
            'list_issue_date' => ['t_s' => 1710510600],
            'auditors' => [],
            'signkeys' => []
        ];
        $this->setupMockResponse($expectedData);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'keys?', [])
            ->willReturn($this->response);

        $result = $this->client->getKeys();
        $this->assertInstanceOf(ExchangeKeysResponse::class, $result);
        $this->assertEquals('test-master-key', $result->master_public_key);
        $this->assertEquals('test-pub', $result->exchange_pub);
    }

    public function testGetKeysAsync(): void
    {
        $expectedData = ['key1' => 'value1'];
        $this->setupMockAsyncResponse($expectedData);

        $this->httpClient->expects($this->once())
            ->method('requestAsync')
            ->with('GET', 'keys?', [])
            ->willReturn($this->promise);

        $promise = $this->client->getKeysAsync();
        $this->assertInstanceOf(Promise::class, $promise);
        
        $result = $promise->wait();
        $this->assertEquals($expectedData, json_decode((string)$result->getBody(), true));
    }

    public function testGetManagementKeys(): void
    {
        $expectedData = [
            'future_denoms' => [],
            'future_signkeys' => [],
            'master_pub' => 'test-master-pub',
            'denom_secmod_public_key' => 'test-denom-key',
            'signkey_secmod_public_key' => 'test-signkey-key'
        ];
        $this->setupMockResponse($expectedData);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'management/keys', [])
            ->willReturn($this->response);

        $result = $this->client->getManagementKeys();
        $this->assertInstanceOf(FutureKeysResponse::class, $result);
        $this->assertEquals('test-master-pub', $result->getMasterPub());
    }

    public function testGetManagementKeysAsync(): void
    {
        $expectedData = ['management_key' => 'value'];
        $this->setupMockAsyncResponse($expectedData);

        $this->httpClient->expects($this->once())
            ->method('requestAsync')
            ->with('GET', 'management/keys', [])
            ->willReturn($this->promise);

        $promise = $this->client->getManagementKeysAsync();
        $this->assertInstanceOf(Promise::class, $promise);
        
        $result = $promise->wait();
        $this->assertEquals($expectedData, json_decode((string)$result->getBody(), true));
    }

    public function testGetTransferSuccess(): void
    {
        $expectedData = [
            'total' => '10',
            'wire_fee' => '1',
            'merchant_pub' => 'test-merchant',
            'h_payto' => 'test-payto-hash',
            'execution_time' => ['t_s' => 1710510600],
            'deposits' => [
                [
                    'h_contract_terms' => 'test-contract',
                    'coin_pub' => 'test-coin',
                    'deposit_value' => '10',
                    'deposit_fee' => '1'
                ]
            ],
            'exchange_sig' => 'test-sig',
            'exchange_pub' => 'test-pub'
        ];
        $this->setupMockResponse($expectedData, 200);
        
        $this->config->method('getWrapResponse')
            ->willReturn(true);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'transfers/test-wtid', [])
            ->willReturn($this->response);

        $result = $this->client->getTransfer('test-wtid');
        $this->assertInstanceOf(TrackTransferResponse::class, $result);
        $this->assertEquals('10', $result->total);
        $this->assertEquals('test-merchant', $result->merchant_pub);
    }

    public function testGetTransferSuccessAsync(): void
    {
        $expectedData = [
            'total' => '10',
            'wire_fee' => '1',
            'merchant_pub' => 'test-merchant',
            'h_payto' => 'test-payto-hash',
            'execution_time' => ['t_s' => 1710510600],
            'deposits' => [
                [
                    'h_contract_terms' => 'test-contract',
                    'coin_pub' => 'test-coin',
                    'deposit_value' => '10',
                    'deposit_fee' => '1'
                ]
            ],
            'exchange_sig' => 'test-sig',
            'exchange_pub' => 'test-pub'
        ];
        $this->setupMockAsyncResponse($expectedData, 200);
        
        $this->config->method('getWrapResponse')
            ->willReturn(true);

        $this->httpClient->expects($this->once())
            ->method('requestAsync')
            ->with('GET', 'transfers/test-wtid', [])
            ->willReturn($this->promise);

        $promise = $this->client->getTransferAsync('test-wtid');
        $this->assertInstanceOf(Promise::class, $promise);
        
        $response = $promise->wait();
        $result = TrackTransferResponse::fromArray(json_decode((string)$response->getBody(), true));

        $this->assertInstanceOf(TrackTransferResponse::class, $result);
        $this->assertEquals('10', $result->total);
        $this->assertEquals('test-merchant', $result->merchant_pub);
    }

    public function testGetTransferThrowsExceptionOnNon200(): void
    {
        $this->setupMockResponse(['error' => 'test'], 404);
        
        $this->config->method('getWrapResponse')->willReturn(true);

        $this->httpClient->method('request')
            ->willReturn($this->response);

        $this->expectException(TalerException::class);
        $this->client->getTransfer('test-wtid');
    }

    public function testGetTransferThrowsExceptionOnNon200Async(): void
    {
        $this->setupMockAsyncResponse(['error' => 'test'], 404);
        
        $this->config->method('getWrapResponse')->willReturn(true);

        $this->httpClient->method('requestAsync')
            ->willReturn($this->promise);

        $promise = $this->client->getTransferAsync('test-wtid');
        $response = $promise->wait();
        $this->expectException(TalerException::class);
        match ($response->getStatusCode()) {
            200 => TrackTransferResponse::fromArray(json_decode((string)$response->getBody(), true)),
            default => throw new TalerException('Unexpected response status code: ' . $response->getStatusCode())
        };
    }

    public function testGetDepositsSuccess200(): void
    {
        $expectedData = [
            'wtid' => 'test-wtid',
            'execution_time' => ['t_s' => 1710510600],
            'coin_contribution' => '10',
            'exchange_sig' => 'test-sig',
            'exchange_pub' => 'test-pub'
        ];
        $this->setupMockResponse($expectedData, 200);
        
        $this->config->method('getWrapResponse')->willReturn(true);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->willReturn($this->response);

        $result = $this->client->getDeposits(
            'h_wire',
            'merchant_pub',
            'h_contract',
            'coin_pub',
            'merchant_sig'
        );

        $this->assertInstanceOf(TrackTransactionResponse::class, $result);
        $this->assertEquals('test-wtid', $result->wtid);
    }

    public function testGetDepositsSuccess200Async(): void
    {
        $expectedData = [
            'wtid' => 'test-wtid',
            'execution_time' => ['t_s' => 1710510600],
            'coin_contribution' => '10',
            'exchange_sig' => 'test-sig',
            'exchange_pub' => 'test-pub'
        ];
        $this->setupMockAsyncResponse($expectedData, 200);
        
        $this->config->method('getWrapResponse')->willReturn(true);

        $this->httpClient->expects($this->once())
            ->method('requestAsync')
            ->willReturn($this->promise);

        $promise = $this->client->getDepositsAsync(
            'h_wire',
            'merchant_pub',
            'h_contract',
            'coin_pub',
            'merchant_sig'
        );
        $this->assertInstanceOf(Promise::class, $promise);
        
        $response = $promise->wait();
        $result = match ($response->getStatusCode()) {
            200 => TrackTransactionResponse::fromArray(json_decode((string)$response->getBody(), true)),
            202 => TrackTransactionAcceptedResponse::fromArray(json_decode((string)$response->getBody(), true)),
            403 => ErrorDetail::fromArray(json_decode((string)$response->getBody(), true)),
            default => throw new TalerException('Unexpected response status code: ' . $response->getStatusCode())
        };
        $this->assertInstanceOf(TrackTransactionResponse::class, $result);
        $this->assertEquals('test-wtid', $result->wtid);
    }

    public function testGetDepositsAccepted202(): void
    {
        $expectedData = [
            'requirement_row' => 1,
            'kyc_ok' => false,
            'execution_time' => ['t_s' => 1710510600],
            'account_pub' => 'test-pub'
        ];
        $this->setupMockResponse($expectedData, 202);
        
        $this->config->method('getWrapResponse')
            ->willReturn(true);

        $result = $this->client->getDeposits(
            'h_wire',
            'merchant_pub',
            'h_contract',
            'coin_pub',
            'merchant_sig'
        );

        $this->assertInstanceOf(TrackTransactionAcceptedResponse::class, $result);
        $this->assertFalse($result->kyc_ok);
    }

    public function testGetDepositsAccepted202Async(): void
    {
        $expectedData = [
            'requirement_row' => 1,
            'kyc_ok' => false,
            'execution_time' => ['t_s' => 1710510600],
            'account_pub' => 'test-pub'
        ];
        $this->setupMockAsyncResponse($expectedData, 202);
        
        $this->config->method('getWrapResponse')->willReturn(true);

        $promise = $this->client->getDepositsAsync(
            'h_wire',
            'merchant_pub',
            'h_contract',
            'coin_pub',
            'merchant_sig'
        );
        $this->assertInstanceOf(Promise::class, $promise);
        
        $response = $promise->wait();
        $result = match ($response->getStatusCode()) {
            200 => TrackTransactionResponse::fromArray(json_decode((string)$response->getBody(), true)),
            202 => TrackTransactionAcceptedResponse::fromArray(json_decode((string)$response->getBody(), true)),
            403 => ErrorDetail::fromArray(json_decode((string)$response->getBody(), true)),
            default => throw new TalerException('Unexpected response status code: ' . $response->getStatusCode())
        };
        $this->assertInstanceOf(TrackTransactionAcceptedResponse::class, $result);
        $this->assertFalse($result->kyc_ok);
    }

    public function testGetDepositsForbidden403(): void
    {
        $expectedData = [
            'code' => 403,
            'hint' => 'Access denied',
            'detail' => 'Invalid signature'
        ];
        $this->setupMockResponse($expectedData, 403);
        
        $this->config->method('getWrapResponse')
            ->willReturn(true);

        $result = $this->client->getDeposits(
            'h_wire',
            'merchant_pub',
            'h_contract',
            'coin_pub',
            'merchant_sig'
        );

        $this->assertInstanceOf(ErrorDetail::class, $result);
        $this->assertEquals(403, $result->code);
    }

    public function testGetDepositsForbidden403Async(): void
    {
        $expectedData = [
            'code' => 403,
            'hint' => 'Access denied',
            'detail' => 'Invalid signature'
        ];
        $this->setupMockAsyncResponse($expectedData, 403);
        
        $this->config->method('getWrapResponse')->willReturn(true);

        $promise = $this->client->getDepositsAsync(
            'h_wire',
            'merchant_pub',
            'h_contract',
            'coin_pub',
            'merchant_sig'
        );
        $this->assertInstanceOf(Promise::class, $promise);
        
        $response = $promise->wait();
        $result = match ($response->getStatusCode()) {
            200 => TrackTransactionResponse::fromArray(json_decode((string)$response->getBody(), true)),
            202 => TrackTransactionAcceptedResponse::fromArray(json_decode((string)$response->getBody(), true)),
            403 => ErrorDetail::fromArray(json_decode((string)$response->getBody(), true)),
            default => throw new TalerException('Unexpected response status code: ' . $response->getStatusCode())
        };
        $this->assertInstanceOf(ErrorDetail::class, $result);
        $this->assertEquals(403, $result->code);
    }

    public function testGetDepositsThrowsExceptionOnUnexpectedStatus(): void
    {
        $this->setupMockResponse(['error' => 'test'], 500);
        
        $this->config->method('getWrapResponse')->willReturn(true);

        $this->expectException(TalerException::class);
        $this->client->getDeposits(
            'h_wire',
            'merchant_pub',
            'h_contract',
            'coin_pub',
            'merchant_sig'
        );
    }

    public function testGetDepositsThrowsExceptionOnUnexpectedStatusAsync(): void
    {
        $this->setupMockAsyncResponse(['error' => 'test'], 500);
        
        $this->config->method('getWrapResponse')->willReturn(true);

        $promise = $this->client->getDepositsAsync(
            'h_wire',
            'merchant_pub',
            'h_contract',
            'coin_pub',
            'merchant_sig'
        );
        $response = $promise->wait();
        $this->expectException(TalerException::class);
        match ($response->getStatusCode()) {
            200 => TrackTransactionResponse::fromArray(json_decode((string)$response->getBody(), true)),
            202 => TrackTransactionAcceptedResponse::fromArray(json_decode((string)$response->getBody(), true)),
            403 => ErrorDetail::fromArray(json_decode((string)$response->getBody(), true)),
            default => throw new TalerException('Unexpected response status code: ' . $response->getStatusCode())
        };
    }

    /**
     * @param array<string, mixed> $data The response data to mock
     */
    private function setupMockResponse(array $data, int $statusCode = 200): void
    {
        $this->stream->method('__toString')
            ->willReturn(json_encode($data));
        
        $this->response->method('getBody')
            ->willReturn($this->stream);
            
        $this->response->method('getStatusCode')
            ->willReturn($statusCode);

        $this->httpClient->method('request')
            ->willReturn($this->response);
    }

    /**
     * @param array<string, mixed> $data The response data to mock
     */
    private function setupMockAsyncResponse(array $data, int $statusCode = 200): void
    {
        $this->stream->method('__toString')
            ->willReturn(json_encode($data));
        
        $this->response->method('getBody')
            ->willReturn($this->stream);
            
        $this->response->method('getStatusCode')
            ->willReturn($statusCode);

        $this->promise->method('wait')
            ->willReturn($this->response);

        $this->httpClient->method('requestAsync')
            ->willReturn($this->promise);
    }
} 
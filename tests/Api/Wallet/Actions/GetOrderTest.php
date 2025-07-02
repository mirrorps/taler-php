<?php

namespace Taler\Tests\Api\Wallet\Actions;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Taler\Api\Wallet\Actions\GetOrder;
use Taler\Api\Wallet\WalletClient;
use Taler\Api\Wallet\Dto\StatusPaidResponse;
use Taler\Api\Wallet\Dto\StatusGotoResponse;
use Taler\Api\Wallet\Dto\StatusUnpaidResponse;
use Taler\Exception\TalerException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Taler\Config\TalerConfig;
use Taler\Taler;
use Taler\Http\HttpClientWrapper;
use GuzzleHttp\Promise\Promise;

class GetOrderTest extends TestCase
{
    private WalletClient $walletClient;
    private ResponseInterface&MockObject $response;
    private StreamInterface&MockObject $stream;
    private LoggerInterface&MockObject $logger;
    private Taler&MockObject $taler;
    private HttpClientWrapper&MockObject $httpClientWrapper;

    protected function setUp(): void
    {
        $this->response = $this->createMock(ResponseInterface::class);
        $this->stream = $this->createMock(StreamInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->taler = $this->createMock(Taler::class);
        
        $this->taler->method('getLogger')->willReturn($this->logger);
        $this->taler->method('getConfig')->willReturn(new TalerConfig('https://example.com', '', true));
        
        // Create HttpClientWrapper mock with request and requestAsync methods
        $this->httpClientWrapper = $this->getMockBuilder(HttpClientWrapper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['request', 'requestAsync'])
            ->getMock();
        
        $this->walletClient = new WalletClient($this->taler, $this->httpClientWrapper);
    }

    public function testRunSuccessPaidResponse(): void
    {
        $expectedData = [
            'refunded' => false,
            'refund_pending' => false,
            'refund_amount' => '0.00',
            'refund_taken' => '0.00'
        ];

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')
            ->willReturn(json_encode($expectedData));
        $this->response->method('getBody')
            ->willReturn($this->stream);

        $orderId = 'test_order_1';
        $params = ['status' => 'completed'];
        $headers = ['X-Test' => 'test'];

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('GET', "orders/$orderId?" . http_build_query($params), $headers)
            ->willReturn($this->response);

        $result = GetOrder::run($this->walletClient, $orderId, $params, $headers);

        $this->assertInstanceOf(StatusPaidResponse::class, $result);
        $this->assertFalse($result->refunded);
        $this->assertFalse($result->refund_pending);
        $this->assertEquals('0.00', $result->refund_amount);
        $this->assertEquals('0.00', $result->refund_taken);
    }

    public function testRunSuccessGotoResponse(): void
    {
        $expectedData = [
            'public_reorder_url' => 'https://example.com/reorder'
        ];

        $this->response->method('getStatusCode')->willReturn(202);
        $this->stream->method('__toString')
            ->willReturn(json_encode($expectedData));
        $this->response->method('getBody')
            ->willReturn($this->stream);

        $orderId = 'test_order_1';
        $params = ['status' => 'pending'];
        $headers = ['X-Test' => 'test'];

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('GET', "orders/$orderId?" . http_build_query($params), $headers)
            ->willReturn($this->response);

        $result = GetOrder::run($this->walletClient, $orderId, $params, $headers);

        $this->assertInstanceOf(StatusGotoResponse::class, $result);
        $this->assertEquals('https://example.com/reorder', $result->public_reorder_url);
    }

    public function testRunSuccessUnpaidResponse(): void
    {
        $expectedData = [
            'taler_pay_uri' => 'taler://pay/example',
            'fulfillment_url' => 'https://example.com/fulfill',
            'already_paid_order_id' => null
        ];

        $this->response->method('getStatusCode')->willReturn(402);
        $this->stream->method('__toString')
            ->willReturn(json_encode($expectedData));
        $this->response->method('getBody')
            ->willReturn($this->stream);

        $orderId = 'test_order_1';
        $params = ['status' => 'unpaid'];
        $headers = ['X-Test' => 'test'];

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('GET', "orders/$orderId?" . http_build_query($params), $headers)
            ->willReturn($this->response);

        $result = GetOrder::run($this->walletClient, $orderId, $params, $headers);

        $this->assertInstanceOf(StatusUnpaidResponse::class, $result);
        $this->assertEquals('taler://pay/example', $result->taler_pay_uri);
        $this->assertEquals('https://example.com/fulfill', $result->fulfillment_url);
        $this->assertNull($result->already_paid_order_id);
    }

    public function testRunWithUnexpectedStatusCode(): void
    {
        $this->expectException(TalerException::class);
        $this->expectExceptionMessage('Unexpected response status code: 404');

        $this->response->method('getStatusCode')->willReturn(404);
        $this->stream->method('__toString')
            ->willReturn(json_encode(['error' => 'Not found']));
        $this->response->method('getBody')
            ->willReturn($this->stream);

        $this->httpClientWrapper->method('request')
            ->willReturn($this->response);

        GetOrder::run($this->walletClient, 'test_order_1');
    }

    public function testRunWithTalerException(): void
    {
        $this->expectException(TalerException::class);

        $this->httpClientWrapper->method('request')
            ->willThrowException(new TalerException('Test exception'));

        GetOrder::run($this->walletClient, 'test_order_1');
    }

    public function testRunWithGenericException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Test generic exception');

        $this->httpClientWrapper->method('request')
            ->willThrowException(new \RuntimeException('Test generic exception'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Taler get public order request failed (wallet API)'));

        GetOrder::run($this->walletClient, 'test_order_1');
    }

    public function testRunAsync(): void
    {
        $expectedData = [
            'refunded' => true,
            'refund_pending' => true,
            'refund_amount' => '10.00',
            'refund_taken' => '5.00'
        ];

        $promise = new Promise();
        
        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')
            ->willReturn(json_encode($expectedData));
        $this->response->method('getBody')
            ->willReturn($this->stream);

        $orderId = 'test_order_1';
        $params = ['status' => 'completed'];
        $headers = ['X-Test' => 'test'];

        $this->httpClientWrapper->expects($this->once())
            ->method('requestAsync')
            ->with('GET', "orders/$orderId?" . http_build_query($params), $headers)
            ->willReturn($promise);

        $result = GetOrder::runAsync($this->walletClient, $orderId, $params, $headers);
        $promise->resolve($this->response);

        $this->assertInstanceOf(StatusPaidResponse::class, $result->wait());
        $this->assertTrue($result->wait()->refunded);
        $this->assertTrue($result->wait()->refund_pending);
        $this->assertEquals('10.00', $result->wait()->refund_amount);
        $this->assertEquals('5.00', $result->wait()->refund_taken);
    }
} 
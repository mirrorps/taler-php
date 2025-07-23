<?php

namespace Taler\Tests\Api\Order\Actions;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Taler\Api\Order\Actions\GetOrder;
use Taler\Api\Order\OrderClient;
use Taler\Api\Order\Dto\CheckPaymentPaidResponse;
use Taler\Api\Order\Dto\CheckPaymentClaimedResponse;
use Taler\Api\Order\Dto\CheckPaymentUnpaidResponse;
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
    private OrderClient $orderClient;
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
        
        $this->httpClientWrapper = $this->getMockBuilder(HttpClientWrapper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['request', 'requestAsync'])
            ->getMock();
        
        $this->orderClient = new OrderClient($this->taler, $this->httpClientWrapper);
    }

    public function testRunSuccessWithPaidStatus(): void
    {
        $orderId = 'test_order_123';
        $expectedData = [
            'order_status' => 'paid',
            'refunded' => false,
            'refund_pending' => false,
            'wired' => true,
            'deposit_total' => '100.00',
            'exchange_code' => 200,
            'exchange_http_status' => 200,
            'refund_amount' => '0.00',
            'contract_terms' => [
                'version' => 0,
                'summary' => 'Test Order',
                'order_id' => $orderId,
                'products' => [],
                'timestamp' => ['t_s' => 1234567890],
                'refund_deadline' => ['t_s' => 1234567890],
                'pay_deadline' => ['t_s' => 1234567890],
                'wire_transfer_deadline' => ['t_s' => 1234567890],
                'merchant_pub' => 'merchant_pub_key',
                'merchant_base_url' => 'https://merchant.example.com/',
                'merchant' => [
                    'name' => 'Test Merchant'
                ],
                'h_wire' => 'wire_hash',
                'wire_method' => 'test',
                'exchanges' => [],
                'nonce' => 'test_nonce'
            ],
            'last_payment' => ['t_s' => 1234567890],
            'wire_details' => [],
            'wire_reports' => [],
            'refund_details' => [],
            'order_status_url' => 'https://example.com/status'
        ];

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')
            ->willReturn(json_encode($expectedData));
        $this->response->method('getBody')
            ->willReturn($this->stream);

        $params = ['include_details' => 'true'];
        $headers = ['X-Test' => 'test'];

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('GET', "private/orders/{$orderId}?" . http_build_query($params), $headers)
            ->willReturn($this->response);

        $result = GetOrder::run($this->orderClient, $orderId, $params, $headers);

        $this->assertInstanceOf(CheckPaymentPaidResponse::class, $result);
        $this->assertEquals('paid', $result->order_status);
        $this->assertEquals('100.00', $result->deposit_total);
        $this->assertEquals('Test Order', $result->contract_terms->summary);
    }

    public function testRunSuccessWithUnpaidStatus(): void
    {
        $orderId = 'test_order_123';
        $expectedData = [
            'order_status' => 'unpaid',
            'taler_pay_uri' => 'taler://pay/example',
            'creation_time' => ['t_s' => 1234567890],
            'summary' => 'Test Order',
            'total_amount' => '100.00',
            'order_status_url' => 'https://example.com/status'
        ];

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')
            ->willReturn(json_encode($expectedData));
        $this->response->method('getBody')
            ->willReturn($this->stream);

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('GET', "private/orders/{$orderId}?", [])
            ->willReturn($this->response);

        $result = GetOrder::run($this->orderClient, $orderId);

        $this->assertInstanceOf(CheckPaymentUnpaidResponse::class, $result);
        $this->assertEquals('unpaid', $result->order_status);
        $this->assertEquals('taler://pay/example', $result->taler_pay_uri);
        $this->assertEquals('100.00', $result->total_amount);
    }

    public function testRunWithTalerException(): void
    {
        $this->expectException(TalerException::class);
        $this->expectExceptionMessage('Test exception');

        $orderId = 'test_order_123';
        $this->httpClientWrapper->method('request')
            ->willThrowException(new TalerException('Test exception'));

        GetOrder::run($this->orderClient, $orderId);
    }

    public function testRunWithGenericException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Test generic exception');

        $orderId = 'test_order_123';
        $this->httpClientWrapper->method('request')
            ->willThrowException(new \RuntimeException('Test generic exception'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Taler get order request failed'));

        GetOrder::run($this->orderClient, $orderId);
    }

    public function testRunAsync(): void
    {
        $orderId = 'test_order_123';
        $expectedData = [
            'order_status' => 'paid',
            'refunded' => false,
            'refund_pending' => false,
            'wired' => true,
            'deposit_total' => '100.00',
            'exchange_code' => 200,
            'exchange_http_status' => 200,
            'refund_amount' => '0.00',
            'contract_terms' => [
                'version' => 0,
                'summary' => 'Test Order',
                'order_id' => $orderId,
                'products' => [],
                'timestamp' => ['t_s' => 1234567890],
                'refund_deadline' => ['t_s' => 1234567890],
                'pay_deadline' => ['t_s' => 1234567890],
                'wire_transfer_deadline' => ['t_s' => 1234567890],
                'merchant_pub' => 'merchant_pub_key',
                'merchant_base_url' => 'https://merchant.example.com/',
                'merchant' => [
                    'name' => 'Test Merchant'
                ],
                'h_wire' => 'wire_hash',
                'wire_method' => 'test',
                'exchanges' => [],
                'nonce' => 'test_nonce'
            ],
            'last_payment' => ['t_s' => 1234567890],
            'wire_details' => [],
            'wire_reports' => [],
            'refund_details' => [],
            'order_status_url' => 'https://example.com/status'
        ];

        $promise = new Promise();
        
        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')
            ->willReturn(json_encode($expectedData));
        $this->response->method('getBody')
            ->willReturn($this->stream);

        $params = ['include_details' => 'true'];
        $headers = ['X-Test' => 'test'];

        $this->httpClientWrapper->expects($this->once())
            ->method('requestAsync')
            ->with('GET', "private/orders/{$orderId}?" . http_build_query($params), $headers)
            ->willReturn($promise);

        $result = GetOrder::runAsync($this->orderClient, $orderId, $params, $headers);
        $promise->resolve($this->response);

        $this->assertInstanceOf(CheckPaymentPaidResponse::class, $result->wait());
        $this->assertEquals('paid', $result->wait()->order_status);
        $this->assertEquals('100.00', $result->wait()->deposit_total);
        $this->assertEquals('Test Order', $result->wait()->contract_terms->summary);
    }
} 
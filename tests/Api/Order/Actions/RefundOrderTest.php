<?php

namespace Taler\Tests\Api\Order\Actions;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Taler\Api\Order\Actions\RefundOrder;
use Taler\Api\Order\OrderClient;
use Taler\Api\Order\Dto\MerchantRefundResponse;
use Taler\Api\Order\Dto\RefundRequest;
use Taler\Exception\TalerException;
use Taler\Exception\PaymentDeniedLegallyException;
use Taler\Api\Order\Dto\PaymentDeniedLegallyResponse;
use Taler\Api\Dto\ErrorDetail;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Taler\Config\TalerConfig;
use Taler\Taler;
use Taler\Http\HttpClientWrapper;
use GuzzleHttp\Promise\Promise;

class RefundOrderTest extends TestCase
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

    public function testRunSuccess(): void
    {
        $orderId = 'test_order_123';
        $refundRequest = new RefundRequest(
            refund: 'EUR:10.00',
            reason: 'Customer dissatisfaction'
        );

        $expectedData = [
            'taler_refund_uri' => 'taler://refund/example',
            'h_contract' => 'HASH123'
        ];

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')
            ->willReturn(json_encode($expectedData));
        $this->response->method('getBody')
            ->willReturn($this->stream);

        $headers = [];
        $requestData = json_encode([
            'refund' => $refundRequest->refund,
            'reason' => $refundRequest->reason
        ]);

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('POST', "private/orders/{$orderId}/refund", $headers, $requestData)
            ->willReturn($this->response);

        $result = RefundOrder::run($this->orderClient, $orderId, $refundRequest);

        $this->assertInstanceOf(MerchantRefundResponse::class, $result);
        $this->assertEquals('taler://refund/example', $result->taler_refund_uri);
        $this->assertEquals('HASH123', $result->h_contract);
    }

    public function testRunWithTalerException(): void
    {
        $orderId = 'test_order_123';
        $refundRequest = new RefundRequest(
            refund: 'EUR:10.00',
            reason: 'Customer dissatisfaction'
        );

        $this->httpClientWrapper->method('request')
            ->willThrowException(new TalerException('Test exception'));

        $this->expectException(TalerException::class);
        $this->expectExceptionMessage('Test exception');

        RefundOrder::run($this->orderClient, $orderId, $refundRequest);
    }

    public function testRunWithTalerExceptionParsesErrorDetail(): void
    {
        $orderId = 'test_order_123';
        $refundRequest = new RefundRequest(
            refund: 'EUR:10.00',
            reason: 'Customer dissatisfaction'
        );

        $errorPayload = json_encode([
            'code' => 1200,
            'hint' => 'Refund not allowed'
        ], JSON_THROW_ON_ERROR);

        $this->stream->method('__toString')->willReturn($errorPayload);
        $this->response->method('getBody')->willReturn($this->stream);

        $this->httpClientWrapper->method('request')
            ->willThrowException(new TalerException('Test exception', 400, null, $this->response));

        try {
            RefundOrder::run($this->orderClient, $orderId, $refundRequest);
            $this->fail('Expected TalerException to be thrown');
        } catch (TalerException $ex) {
            $dto = $ex->getResponseDTO();
            $this->assertInstanceOf(ErrorDetail::class, $dto);
            $this->assertSame(1200, $dto->code);
            $this->assertSame('Refund not allowed', $dto->hint);
        }
    }

    public function testRunWithGenericException(): void
    {
        $orderId = 'test_order_123';
        $refundRequest = new RefundRequest(
            refund: 'EUR:10.00',
            reason: 'Customer dissatisfaction'
        );

        $this->httpClientWrapper->method('request')
            ->willThrowException(new \RuntimeException('Test generic exception'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Taler refund request failed'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Test generic exception');

        RefundOrder::run($this->orderClient, $orderId, $refundRequest);
    }

    public function testRunAsync(): void
    {
        $orderId = 'test_order_123';
        $refundRequest = new RefundRequest(
            refund: 'EUR:10.00',
            reason: 'Customer dissatisfaction'
        );

        $expectedData = [
            'taler_refund_uri' => 'taler://refund/example',
            'h_contract' => 'HASH123'
        ];

        $promise = new Promise();
        
        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')
            ->willReturn(json_encode($expectedData));
        $this->response->method('getBody')
            ->willReturn($this->stream);

        $headers = [];
        $requestData = json_encode([
            'refund' => $refundRequest->refund,
            'reason' => $refundRequest->reason
        ]);

        $this->httpClientWrapper->expects($this->once())
            ->method('requestAsync')
            ->with('POST', "private/orders/{$orderId}/refund", $headers, $requestData)
            ->willReturn($promise);

        $result = RefundOrder::runAsync($this->orderClient, $orderId, $refundRequest);
        $promise->resolve($this->response);

        $this->assertInstanceOf(MerchantRefundResponse::class, $result->wait());
        $this->assertEquals('taler://refund/example', $result->wait()->taler_refund_uri);
        $this->assertEquals('HASH123', $result->wait()->h_contract);
    }

    public function testRunPaymentDeniedLegallyException(): void
    {
        $orderId = 'test_order_123';
        $refundRequest = new RefundRequest(
            refund: 'EUR:10.00',
            reason: 'Customer dissatisfaction'
        );

        $body = [
            'exchange_base_urls' => [
                'https://ex1.example.com',
                'https://ex2.example.com',
            ],
        ];

        // 451 Unavailable For Legal Reasons -> PaymentDeniedLegallyException
        $this->response->method('getStatusCode')->willReturn(451);
        $this->stream->method('__toString')
            ->willReturn(json_encode($body));
        $this->response->method('getBody')
            ->willReturn($this->stream);

        $headers = [];
        $requestData = json_encode([
            'refund' => $refundRequest->refund,
            'reason' => $refundRequest->reason
        ]);

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('POST', "private/orders/{$orderId}/refund", $headers, $requestData)
            ->willReturn($this->response);

        try {
            RefundOrder::run($this->orderClient, $orderId, $refundRequest);
            $this->fail('Expected PaymentDeniedLegallyException to be thrown');
        } catch (PaymentDeniedLegallyException $ex) {
            $this->assertSame(PaymentDeniedLegallyException::HTTP_STATUS_CODE, $ex->getCode());
            $dto = $ex->getResponseDTO();
            $this->assertInstanceOf(PaymentDeniedLegallyResponse::class, $dto);
            $this->assertSame(['https://ex1.example.com', 'https://ex2.example.com'], $dto->exchange_base_urls);
        }
    }
} 
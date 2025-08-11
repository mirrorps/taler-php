<?php

namespace Taler\Tests\Api\Order\Actions;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Taler\Api\Order\Actions\ForgetOrder;
use Taler\Api\Order\OrderClient;
use Taler\Api\Order\Dto\ForgetRequest;
use Taler\Exception\TalerException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Taler\Config\TalerConfig;
use Taler\Taler;
use Taler\Http\HttpClientWrapper;
use GuzzleHttp\Promise\Promise;

class ForgetOrderTest extends TestCase
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
        $forgetRequest = new ForgetRequest([
            '$.wire_fee',
            '$.products[0].description'
        ]);

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn('null');
        $this->response->method('getBody')->willReturn($this->stream);

        $headers = [];
        $requestData = json_encode([
            'fields' => $forgetRequest->fields,
        ]);

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('PATCH', "private/orders/{$orderId}/forget", $headers, $requestData)
            ->willReturn($this->response);

        // Should complete without throwing
        ForgetOrder::run($this->orderClient, $orderId, $forgetRequest);
        // Count one assertion to avoid risky test detection
        $this->addToAssertionCount(1);
    }

    public function testRunWithTalerException(): void
    {
        $orderId = 'test_order_123';
        $forgetRequest = new ForgetRequest([
            '$.wire_fee',
        ]);

        $this->httpClientWrapper->method('request')
            ->willThrowException(new TalerException('Test exception'));

        $this->expectException(TalerException::class);
        $this->expectExceptionMessage('Test exception');

        ForgetOrder::run($this->orderClient, $orderId, $forgetRequest);
    }

    public function testRunWithGenericException(): void
    {
        $orderId = 'test_order_123';
        $forgetRequest = new ForgetRequest([
            '$.wire_fee',
        ]);

        $this->httpClientWrapper->method('request')
            ->willThrowException(new \RuntimeException('Test generic exception'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Taler forget request failed'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Test generic exception');

        ForgetOrder::run($this->orderClient, $orderId, $forgetRequest);
    }

    public function testRunAsync(): void
    {
        $orderId = 'test_order_123';
        $forgetRequest = new ForgetRequest([
            '$.wire_fee',
        ]);

        $promise = new Promise();

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn('null');
        $this->response->method('getBody')->willReturn($this->stream);

        $headers = [];
        $requestData = json_encode([
            'fields' => $forgetRequest->fields,
        ]);

        $this->httpClientWrapper->expects($this->once())
            ->method('requestAsync')
            ->with('PATCH', "private/orders/{$orderId}/forget", $headers, $requestData)
            ->willReturn($promise);

        $result = ForgetOrder::runAsync($this->orderClient, $orderId, $forgetRequest);
        $promise->resolve($this->response);

        // Expect the promise to resolve without errors; handleWrappedResponse returns void for 204
        $this->assertNull($result->wait());
    }
}



<?php

namespace Taler\Tests\Api\Order\Actions;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Taler\Api\Order\Actions\CreateOrder;
use Taler\Api\Order\OrderClient;
use Taler\Api\Order\Dto\PostOrderRequest;
use Taler\Api\Order\Dto\PostOrderResponse;
use Taler\Api\Order\Dto\OrderV1;
use Taler\Api\Order\Dto\OrderChoice;
use Taler\Exception\TalerException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Taler\Config\TalerConfig;
use Taler\Taler;
use Taler\Http\HttpClientWrapper;
use GuzzleHttp\Promise\Promise;

class CreateOrderTest extends TestCase
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
        $choices = [
            new OrderChoice(
                amount: '50.00'
            )
        ];
        
        $order = new OrderV1(version: 1, summary: 'Test order', choices: $choices);
        
        $postOrderRequest = new PostOrderRequest(
            order: $order
        );

        $expectedData = [
            'order_id' => 'test_order_123',
            'token' => 'test_token_456'
        ];

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')
            ->willReturn(json_encode($expectedData));
        $this->response->method('getBody')
            ->willReturn($this->stream);

        $headers = [];
        $requestData = json_encode($postOrderRequest);

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('POST', "private/orders", $headers, $requestData)
            ->willReturn($this->response);

        $result = CreateOrder::run($this->orderClient, $postOrderRequest);

        $this->assertInstanceOf(PostOrderResponse::class, $result);
        $this->assertEquals('test_order_123', $result->order_id);
        $this->assertEquals('test_token_456', $result->token);
    }

    public function testRunWithTalerException(): void
    {
        $choices = [
            new OrderChoice(
                amount: '50.00'
            )
        ];
        
        $order = new OrderV1(version: 1, summary: 'Test order', choices: $choices);
        
        $postOrderRequest = new PostOrderRequest(
            order: $order
        );

        $this->httpClientWrapper->method('request')
            ->willThrowException(new TalerException('Test exception'));

        $this->expectException(TalerException::class);
        $this->expectExceptionMessage('Test exception');

        CreateOrder::run($this->orderClient, $postOrderRequest);
    }

    public function testRunWithGenericException(): void
    {
        $choices = [
            new OrderChoice(
                amount: '50.00'
            )
        ];
        
        $order = new OrderV1(version: 1, summary: 'Test order', choices: $choices);
        
        $postOrderRequest = new PostOrderRequest(
            order: $order
        );

        $this->httpClientWrapper->method('request')
            ->willThrowException(new \RuntimeException('Test generic exception'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Taler create order request failed'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Test generic exception');

        CreateOrder::run($this->orderClient, $postOrderRequest);
    }

    public function testRunAsync(): void
    {
        $choices = [
            new OrderChoice(
                amount: '50.00'
            )
        ];
        
        $order = new OrderV1(version: 1, summary: 'Test order', choices: $choices);
        
        $postOrderRequest = new PostOrderRequest(
            order: $order
        );

        $expectedData = [
            'order_id' => 'test_order_123',
            'token' => 'test_token_456'
        ];

        $promise = new Promise();
        
        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')
            ->willReturn(json_encode($expectedData));
        $this->response->method('getBody')
            ->willReturn($this->stream);

        $headers = [];
        $requestData = json_encode($postOrderRequest);

        $this->httpClientWrapper->expects($this->once())
            ->method('requestAsync')
            ->with('POST', "private/orders", $headers, $requestData)
            ->willReturn($promise);

        $result = CreateOrder::runAsync($this->orderClient, $postOrderRequest);
        $promise->resolve($this->response);

        $this->assertInstanceOf(PostOrderResponse::class, $result->wait());
        $this->assertEquals('test_order_123', $result->wait()->order_id);
        $this->assertEquals('test_token_456', $result->wait()->token);
    }
}
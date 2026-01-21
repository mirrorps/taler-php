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
use Taler\Exception\OutOfStockException;
use Taler\Exception\PaymentDeniedLegallyException;
use Taler\Api\Dto\ErrorDetail;
use Taler\Api\Inventory\Dto\OutOfStockResponse;
use Taler\Api\Order\Dto\PaymentDeniedLegallyResponse;
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
                amount: 'EUR:50.00'
            )
        ];
        
        $order = new OrderV1(summary: 'Test order', choices: $choices, fulfillment_message: 'ok');
        
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
                amount: 'EUR:50.00'
            )
        ];
        
        $order = new OrderV1(summary: 'Test order', choices: $choices, fulfillment_message: 'ok');
        
        $postOrderRequest = new PostOrderRequest(
            order: $order
        );

        $this->httpClientWrapper->method('request')
            ->willThrowException(new TalerException('Test exception'));

        $this->expectException(TalerException::class);
        $this->expectExceptionMessage('Test exception');

        CreateOrder::run($this->orderClient, $postOrderRequest);
    }

    public function testRunWithTalerExceptionParsesErrorDetail(): void
    {
        $choices = [
            new OrderChoice(
                amount: 'EUR:50.00'
            )
        ];

        $order = new OrderV1(summary: 'Test order', choices: $choices, fulfillment_message: 'ok');

        $postOrderRequest = new PostOrderRequest(
            order: $order
        );

        $errorPayload = json_encode([
            'code' => 9001,
            'hint' => 'Bad request'
        ], JSON_THROW_ON_ERROR);

        $this->stream->method('__toString')->willReturn($errorPayload);
        $this->response->method('getBody')->willReturn($this->stream);

        $this->httpClientWrapper->method('request')
            ->willThrowException(new TalerException('Test exception', 400, null, $this->response));

        try {
            CreateOrder::run($this->orderClient, $postOrderRequest);
            $this->fail('Expected TalerException to be thrown');
        } catch (TalerException $ex) {
            $dto = $ex->getResponseDTO();
            $this->assertInstanceOf(ErrorDetail::class, $dto);
            $this->assertSame(9001, $dto->code);
            $this->assertSame('Bad request', $dto->hint);
        }
    }

    public function testRunWithGenericException(): void
    {
        $choices = [
            new OrderChoice(
                amount: 'EUR:50.00'
            )
        ];
        
        $order = new OrderV1(summary: 'Test order', choices: $choices, fulfillment_message: 'ok');
        
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
                amount: 'EUR:50.00'
            )
        ];
        
        $order = new OrderV1(summary: 'Test order', choices: $choices, fulfillment_message: 'ok');
        
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

    public function testRunOutOfStockException(): void
    {
        $choices = [
            new OrderChoice(
                amount: 'EUR:50.00'
            )
        ];

        $order = new OrderV1(summary: 'Test order', choices: $choices, fulfillment_message: 'ok');

        $postOrderRequest = new PostOrderRequest(
            order: $order
        );

        $body = [
            'product_id' => 'p-123',
            'requested_quantity' => 5,
            'available_quantity' => 2,
            'restock_expected' => ['t_s' => 1731000000],
        ];

        // 410 Gone -> OutOfStockException
        $this->response->method('getStatusCode')->willReturn(410);
        $this->stream->method('__toString')
            ->willReturn(json_encode($body));
        $this->response->method('getBody')
            ->willReturn($this->stream);

        $headers = [];
        $requestData = json_encode($postOrderRequest);

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('POST', "private/orders", $headers, $requestData)
            ->willReturn($this->response);

        try {
            CreateOrder::run($this->orderClient, $postOrderRequest);
            $this->fail('Expected OutOfStockException to be thrown');
        } catch (OutOfStockException $ex) {
            $this->assertSame(410, $ex->getCode());
            $dto = $ex->getResponseDTO();
            $this->assertInstanceOf(OutOfStockResponse::class, $dto);
            $this->assertSame('p-123', $dto->product_id);
            $this->assertSame(5, $dto->requested_quantity);
            $this->assertSame(2, $dto->available_quantity);
            $this->assertNotNull($dto->restock_expected);
            $this->assertSame(1731000000, $dto->restock_expected?->t_s);
        }
    }

    public function testRunPaymentDeniedLegallyException(): void
    {
        $choices = [
            new OrderChoice(
                amount: 'EUR:50.00'
            )
        ];

        $order = new OrderV1(summary: 'Test order', choices: $choices, fulfillment_message: 'ok');

        $postOrderRequest = new PostOrderRequest(
            order: $order
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
        $requestData = json_encode($postOrderRequest);

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('POST', "private/orders", $headers, $requestData)
            ->willReturn($this->response);

        try {
            CreateOrder::run($this->orderClient, $postOrderRequest);
            $this->fail('Expected PaymentDeniedLegallyException to be thrown');
        } catch (PaymentDeniedLegallyException $ex) {
            $this->assertSame(451, $ex->getCode());
            $dto = $ex->getResponseDTO();
            $this->assertInstanceOf(PaymentDeniedLegallyResponse::class, $dto);
            $this->assertSame(['https://ex1.example.com', 'https://ex2.example.com'], $dto->exchange_base_urls);
        }
    }
}
<?php

namespace Taler\Tests\Api\Order\Actions;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Taler\Api\Order\Actions\GetOrders;
use Taler\Api\Order\OrderClient;
use Taler\Api\Order\Dto\GetOrdersRequest;
use Taler\Api\Order\Dto\OrderHistory;
use Taler\Exception\TalerException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Taler\Config\TalerConfig;
use Taler\Taler;
use Taler\Http\HttpClientWrapper;
use GuzzleHttp\Promise\Promise;

class GetOrdersTest extends TestCase
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
        
        // Create HttpClientWrapper mock with request and requestAsync methods
        $this->httpClientWrapper = $this->getMockBuilder(HttpClientWrapper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['request', 'requestAsync'])
            ->getMock();
        
        $this->orderClient = new OrderClient($this->taler, $this->httpClientWrapper);
    }

    public function testRunSuccess(): void
    {
        $expectedData = [
            'orders' => [
                [
                    'order_id' => 'test_order_1',
                    'row_id' => 1,
                    'timestamp' => ['t_s' => 1234567890],
                    'amount' => '10.00',
                    'summary' => 'Test Order 1',
                    'refundable' => true,
                    'paid' => true
                ],
                [
                    'order_id' => 'test_order_2',
                    'row_id' => 2,
                    'timestamp' => ['t_s' => 1234567891],
                    'amount' => '20.00',
                    'summary' => 'Test Order 2',
                    'refundable' => false,
                    'paid' => true
                ]
            ]
        ];

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')
            ->willReturn(json_encode($expectedData));
        $this->response->method('getBody')
            ->willReturn($this->stream);

        $request = new GetOrdersRequest(limit: -20, paid: true);
        $headers = ['X-Test' => 'test'];

        $params = $request->toArray();
        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('GET', 'private/orders?' . http_build_query($params), $headers)
            ->willReturn($this->response);

        $result = GetOrders::run($this->orderClient, $request, $headers);

        $this->assertInstanceOf(OrderHistory::class, $result);
        $this->assertCount(2, $result->orders);
        $this->assertEquals($expectedData['orders'][0]['order_id'], $result->orders[0]->order_id);
        $this->assertEquals($expectedData['orders'][0]['amount'], $result->orders[0]->amount);
        $this->assertEquals($expectedData['orders'][1]['order_id'], $result->orders[1]->order_id);
        $this->assertEquals($expectedData['orders'][1]['amount'], $result->orders[1]->amount);
    }

    public function testRunWithException(): void
    {
        $this->expectException(TalerException::class);

        $this->httpClientWrapper->method('request')
            ->willThrowException(new TalerException('Test exception'));

        GetOrders::run($this->orderClient);
    }

    public function testRunWithGenericException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Test generic exception');

        $this->httpClientWrapper->method('request')
            ->willThrowException(new \RuntimeException('Test generic exception'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Taler get orders request failed'));

        GetOrders::run($this->orderClient);
    }

    public function testRunAsync(): void
    {
        $expectedData = [
            'orders' => [
                [
                    'order_id' => 'test_order_1',
                    'row_id' => 1,
                    'timestamp' => ['t_s' => 1234567890],
                    'amount' => '10.00',
                    'summary' => 'Test Order 1',
                    'refundable' => true,
                    'paid' => true
                ]
            ]
        ];

        $promise = new Promise();
        
        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')
            ->willReturn(json_encode($expectedData));
        $this->response->method('getBody')
            ->willReturn($this->stream);

        $request = new GetOrdersRequest(limit: 1, paid: false);
        $headers = ['X-Test' => 'test'];

        $params = $request->toArray();
        $this->httpClientWrapper->expects($this->once())
            ->method('requestAsync')
            ->with('GET', 'private/orders?' . http_build_query($params), $headers)
            ->willReturn($promise);

        $result = GetOrders::runAsync($this->orderClient, $request, $headers);
        $promise->resolve($this->response);

        $this->assertInstanceOf(OrderHistory::class, $result->wait());
        $this->assertCount(1, $result->wait()->orders);
        $this->assertEquals($expectedData['orders'][0]['order_id'], $result->wait()->orders[0]->order_id);
        $this->assertEquals($expectedData['orders'][0]['amount'], $result->wait()->orders[0]->amount);
    }
} 
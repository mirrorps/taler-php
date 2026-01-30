<?php

namespace Taler\Tests\Api\Order;

use GuzzleHttp\Promise\Promise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Taler\Api\Order\Dto\ForgetRequest;
use Taler\Api\Order\Dto\PostOrderRequest;
use Taler\Api\Order\Dto\PostOrderResponse;
use Taler\Api\Order\Dto\RefundRequest;
use Taler\Api\Order\Dto\OrderV1;
use Taler\Api\Order\Dto\OrderChoice;
use Taler\Api\Order\Dto\OrderHistory;
use Taler\Api\Order\Dto\GetOrdersRequest;
use Taler\Api\Order\Dto\CheckPaymentPaidResponse;
use Taler\Api\Order\OrderClient;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;
use Taler\Api\Dto\ErrorDetail;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

class OrderClientTest extends TestCase
{
    private OrderClient $client;
    private ResponseInterface&MockObject $response;
    private StreamInterface&MockObject $stream;
    private LoggerInterface&MockObject $logger;
    private Taler&MockObject $taler;
    private HttpClientWrapper&MockObject $httpClient;

    protected function setUp(): void
    {
        $this->response = $this->createMock(ResponseInterface::class);
        $this->stream = $this->createMock(StreamInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->taler = $this->createMock(Taler::class);

        $this->taler->method('getLogger')->willReturn($this->logger);
        $this->taler->method('getConfig')->willReturn(new TalerConfig('https://example.com', '', true));

        $this->httpClient = $this->getMockBuilder(HttpClientWrapper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['request', 'requestAsync'])
            ->getMock();

        $this->client = new OrderClient($this->taler, $this->httpClient);
    }

    public function testCreateOrder(): void
    {
        $choices = [new OrderChoice(amount: 'KUDOS:1')];
        $order = new OrderV1(summary: 'Order V1', choices: $choices, fulfillment_message: 'ok');
        $request = new PostOrderRequest(order: $order);

        $expected = ['order_id' => 'ord_1', 'token' => 'tok_1'];
        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn(json_encode($expected));
        $this->response->method('getBody')->willReturn($this->stream);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('POST', 'private/orders', [], json_encode($request))
            ->willReturn($this->response);

        $result = $this->client->createOrder($request);

        $this->assertInstanceOf(PostOrderResponse::class, $result);
        $this->assertSame('ord_1', $result->order_id);
        $this->assertSame('tok_1', $result->token);
    }

    public function testCreateOrderAsync(): void
    {
        $choices = [new OrderChoice(amount: 'KUDOS:1')];
        $order = new OrderV1(summary: 'Order V1', choices: $choices, fulfillment_message: 'ok');
        $request = new PostOrderRequest(order: $order);

        $expected = ['order_id' => 'ord_2', 'token' => 'tok_2'];
        $promise = new Promise();

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn(json_encode($expected));
        $this->response->method('getBody')->willReturn($this->stream);

        $this->httpClient->expects($this->once())
            ->method('requestAsync')
            ->with('POST', 'private/orders', [], json_encode($request))
            ->willReturn($promise);

        $async = $this->client->createOrderAsync($request);
        $promise->resolve($this->response);

        $this->assertInstanceOf(PostOrderResponse::class, $async->wait());
        $this->assertSame('ord_2', $async->wait()->order_id);
    }

    public function testGetOrders(): void
    {
        $expected = [
            'orders' => [
                [
                    'order_id' => 'o1',
                    'row_id' => 1,
                    'timestamp' => ['t_s' => 111],
                    'amount' => 'KUDOS:1',
                    'summary' => 'S1',
                    'refundable' => true,
                    'paid' => true,
                ],
            ],
        ];

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn(json_encode($expected));
        $this->response->method('getBody')->willReturn($this->stream);

        $request = new GetOrdersRequest(limit: -20, paid: true);
        $headers = ['X-Test' => 'y'];

        $params = $request->toArray();
        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'private/orders?' . http_build_query($params), $headers)
            ->willReturn($this->response);

        $result = $this->client->getOrders($request, $headers);
        $this->assertInstanceOf(OrderHistory::class, $result);
        $this->assertCount(1, $result->orders);
        $this->assertSame('o1', $result->orders[0]->order_id);
    }

    public function testGetOrder(): void
    {
        $expected = [
            'order_status' => 'paid',
            'refunded' => false,
            'refund_pending' => false,
            'wired' => true,
            'deposit_total' => 'KUDOS:1',
            'exchange_code' => 200,
            'exchange_http_status' => 200,
            'refund_amount' => 'KUDOS:0',
            'contract_terms' => [
                'version' => 0,
                'amount' => 'KUDOS:1',
                'max_fee' => 'KUDOS:0',
                'summary' => 'S',
                'order_id' => 'o1',
                'products' => [],
                'timestamp' => ['t_s' => 1],
                'refund_deadline' => ['t_s' => 2],
                'pay_deadline' => ['t_s' => 3],
                'wire_transfer_deadline' => ['t_s' => 4],
                'merchant_pub' => 'mp',
                'merchant_base_url' => 'https://merchant/',
                'merchant' => ['name' => 'M'],
                'h_wire' => 'w',
                'wire_method' => 'wm',
                'exchanges' => [],
                'nonce' => 'n',
            ],
            'last_payment' => ['t_s' => 5],
            'wire_details' => [],
            'wire_reports' => [],
            'refund_details' => [],
            'order_status_url' => 'https://status'
        ];

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn(json_encode($expected));
        $this->response->method('getBody')->willReturn($this->stream);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'private/orders/o1?', [])
            ->willReturn($this->response);

        $result = $this->client->getOrder('o1');
        $this->assertInstanceOf(CheckPaymentPaidResponse::class, $result);
        $this->assertSame('paid', $result->order_status);
    }

    public function testRefundOrder(): void
    {
        $expected = ['taler_refund_uri' => 'taler://refund/abc', 'h_contract' => 'hash123'];
        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn(json_encode($expected));
        $this->response->method('getBody')->willReturn($this->stream);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->willReturn($this->response);

        $result = $this->client->refundOrder('o1', new RefundRequest(refund: 'KUDOS:1', reason: 'r'));
        $this->assertIsObject($result);
        $this->assertSame('taler://refund/abc', $result->taler_refund_uri);
        $this->assertSame('hash123', $result->h_contract);
    }

    public function testDeleteOrder(): void
    {
        $this->response->method('getStatusCode')->willReturn(204);
        $this->stream->method('__toString')->willReturn('');
        $this->response->method('getBody')->willReturn($this->stream);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('DELETE', 'private/orders/o1', [])
            ->willReturn($this->response);

        $this->client->deleteOrder('o1');
        $this->addToAssertionCount(1); // reached without exception
    }

    public function testForgetOrder(): void
    {
        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn('');
        $this->response->method('getBody')->willReturn($this->stream);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->willReturn($this->response);

        $this->client->forgetOrder('o1', new ForgetRequest(fields: ['$.merchant'])) ;
        $this->addToAssertionCount(1);
    }

    public function testCreateOrderThrowsTalerException(): void
    {
        $this->expectException(TalerException::class);

        $choices = [new OrderChoice(amount: 'KUDOS:1')];
        $order = new OrderV1(summary: 'Order V1', choices: $choices, fulfillment_message: 'ok');
        $request = new PostOrderRequest(order: $order);

        $this->httpClient->method('request')
            ->willThrowException(new TalerException('fail'));

        $this->client->createOrder($request);
    }

    public function testCreateOrderThrowsTalerExceptionWithErrorDetail(): void
    {
        $choices = [new OrderChoice(amount: 'KUDOS:1')];
        $order = new OrderV1(summary: 'Order V1', choices: $choices, fulfillment_message: 'ok');
        $request = new PostOrderRequest(order: $order);

        /** @var ResponseInterface&MockObject $response */
        $response = $this->createMock(ResponseInterface::class);
        /** @var StreamInterface&MockObject $stream */
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('__toString')->willReturn(json_encode(['code' => 777, 'hint' => 'oops']));
        $response->method('getBody')->willReturn($stream);

        $this->httpClient->method('request')
            ->willThrowException(new TalerException('fail', 400, null, $response));

        try {
            $this->client->createOrder($request);
            $this->fail('Expected TalerException');
        } catch (TalerException $ex) {
            $dto = $ex->getResponseDTO();
            $this->assertInstanceOf(ErrorDetail::class, $dto);
            $this->assertSame(777, $dto->code);
            $this->assertSame('oops', $dto->hint);
        }
    }
}



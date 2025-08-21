<?php

namespace Taler\Tests\Api\Inventory\Actions;

use GuzzleHttp\Promise\Promise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Taler\Api\Inventory\Actions\GetProducts;
use Taler\Api\Inventory\Dto\GetProductsRequest;
use Taler\Api\Inventory\Dto\InventoryEntry;
use Taler\Api\Inventory\Dto\InventorySummaryResponse;
use Taler\Api\Inventory\InventoryClient;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

class GetProductsTest extends TestCase
{
    private InventoryClient $client;
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

        $this->client = new InventoryClient($this->taler, $this->httpClientWrapper);
    }

    public function testRunSuccess(): void
    {
        $expected = [
            'products' => [
                ['product_id' => 'p1', 'product_serial' => 1],
                ['product_id' => 'p2', 'product_serial' => 2],
            ]
        ];

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn(json_encode($expected));
        $this->response->method('getBody')->willReturn($this->stream);

        $headers = ['X-Test' => 'test'];
        $request = new GetProductsRequest(limit: 10, offset: 100);

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('GET', 'private/products?limit=10&offset=100', $headers)
            ->willReturn($this->response);

        $result = GetProducts::run($this->client, $request, $headers);

        $this->assertInstanceOf(InventorySummaryResponse::class, $result);
        $this->assertCount(2, $result->products);
        $this->assertInstanceOf(InventoryEntry::class, $result->products[0]);
        $this->assertSame('p1', $result->products[0]->product_id);
    }

    public function testRunWithTalerException(): void
    {
        $this->expectException(TalerException::class);

        $this->httpClientWrapper->method('request')->willThrowException(new TalerException('boom'));
        GetProducts::run($this->client);
    }

    public function testRunAsync(): void
    {
        $expected = [
            'products' => [
                ['product_id' => 'p1', 'product_serial' => 1],
            ]
        ];

        $promise = new Promise();

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn(json_encode($expected));
        $this->response->method('getBody')->willReturn($this->stream);

        $this->httpClientWrapper->expects($this->once())
            ->method('requestAsync')
            ->with('GET', 'private/products', [])
            ->willReturn($promise);

        $result = GetProducts::runAsync($this->client);
        $promise->resolve($this->response);

        $this->assertInstanceOf(InventorySummaryResponse::class, $result->wait());
        $this->assertSame('p1', $result->wait()->products[0]->product_id);
    }
}



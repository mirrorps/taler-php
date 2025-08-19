<?php

namespace Taler\Tests\Api\Inventory\Actions;

use GuzzleHttp\Promise\Promise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Taler\Api\Inventory\Actions\GetCategory;
use Taler\Api\Inventory\Dto\CategoryProductList;
use Taler\Api\Inventory\InventoryClient;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

class GetCategoryTest extends TestCase
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
            'name' => 'Beverages',
            'products' => [
                ['product_id' => 'prod-1'],
                ['product_id' => 'prod-2'],
            ]
        ];

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn(json_encode($expected));
        $this->response->method('getBody')->willReturn($this->stream);

        $headers = ['X-Test' => 'test'];

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('GET', 'private/categories/5', $headers)
            ->willReturn($this->response);

        $result = GetCategory::run($this->client, 5, $headers);

        $this->assertInstanceOf(CategoryProductList::class, $result);
        $this->assertSame('Beverages', $result->name);
        $this->assertCount(2, $result->products);
    }

    public function testRunWithTalerException(): void
    {
        $this->expectException(TalerException::class);

        $this->httpClientWrapper->method('request')->willThrowException(new TalerException('boom'));
        GetCategory::run($this->client, 10);
    }

    public function testRunAsync(): void
    {
        $expected = [
            'name' => 'Beverages',
            'products' => [
                ['product_id' => 'prod-1'],
            ]
        ];

        $promise = new Promise();

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn(json_encode($expected));
        $this->response->method('getBody')->willReturn($this->stream);

        $this->httpClientWrapper->expects($this->once())
            ->method('requestAsync')
            ->with('GET', 'private/categories/7', [])
            ->willReturn($promise);

        $result = GetCategory::runAsync($this->client, 7);
        $promise->resolve($this->response);

        $this->assertInstanceOf(CategoryProductList::class, $result->wait());
        $this->assertSame('Beverages', $result->wait()->name);
        $this->assertSame('prod-1', $result->wait()->products[0]->product_id);
    }
}



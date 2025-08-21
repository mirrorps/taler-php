<?php

namespace Taler\Tests\Api\Inventory\Actions;

use GuzzleHttp\Promise\Promise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Taler\Api\Inventory\Actions\GetProduct;
use Taler\Api\Inventory\Dto\ProductDetail;
use Taler\Api\Inventory\InventoryClient;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

class GetProductTest extends TestCase
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
            'product_name' => 'Name',
            'description' => 'Desc',
            'description_i18n' => ['en' => 'Desc'],
            'unit' => 'kg',
            'categories' => [1],
            'price' => 'EUR:1',
            'image' => 'data:image/png;base64,AAAA',
            'total_stock' => 10,
            'total_sold' => 5,
            'total_lost' => 0,
        ];

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn(json_encode($expected));
        $this->response->method('getBody')->willReturn($this->stream);

        $headers = ['X-Test' => 'test'];

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('GET', 'private/products/sku-1', $headers)
            ->willReturn($this->response);

        $result = GetProduct::run($this->client, 'sku-1', $headers);

        $this->assertInstanceOf(ProductDetail::class, $result);
        $this->assertSame('Name', $result->product_name);
        $this->assertSame('kg', $result->unit);
    }

    public function testRunWithTalerException(): void
    {
        $this->expectException(TalerException::class);

        $this->httpClientWrapper->method('request')->willThrowException(new TalerException('boom'));
        GetProduct::run($this->client, 'sku-1');
    }

    public function testRunAsync(): void
    {
        $expected = [
            'product_name' => 'Name',
            'description' => 'Desc',
            'description_i18n' => ['en' => 'Desc'],
            'unit' => 'kg',
            'categories' => [1],
            'price' => 'EUR:1',
            'image' => 'data:image/png;base64,AAAA',
            'total_stock' => 10,
            'total_sold' => 5,
            'total_lost' => 0,
        ];

        $promise = new Promise();

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn(json_encode($expected));
        $this->response->method('getBody')->willReturn($this->stream);

        $this->httpClientWrapper->expects($this->once())
            ->method('requestAsync')
            ->with('GET', 'private/products/sku-2', [])
            ->willReturn($promise);

        $result = GetProduct::runAsync($this->client, 'sku-2');
        $promise->resolve($this->response);

        $this->assertInstanceOf(ProductDetail::class, $result->wait());
        $this->assertSame('Name', $result->wait()->product_name);
    }
}



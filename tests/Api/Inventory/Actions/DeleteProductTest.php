<?php

namespace Taler\Tests\Api\Inventory\Actions;

use GuzzleHttp\Promise\Promise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Taler\Api\Inventory\Actions\DeleteProduct;
use Taler\Api\Inventory\InventoryClient;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

class DeleteProductTest extends TestCase
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

    public function testRunSuccess204(): void
    {
        $this->response->method('getStatusCode')->willReturn(204);
        $this->stream->method('__toString')->willReturn('');
        $this->response->method('getBody')->willReturn($this->stream);

        $headers = ['X-Test' => 'test'];

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('DELETE', 'private/products/sku-1', $headers)
            ->willReturn($this->response);

        DeleteProduct::run($this->client, 'sku-1', $headers);
        $this->addToAssertionCount(1);
    }

    public function testRunSuccess404(): void
    {
        $this->response->method('getStatusCode')->willReturn(404);
        $this->stream->method('__toString')->willReturn(json_encode(['code' => 404]));
        $this->response->method('getBody')->willReturn($this->stream);

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('DELETE', 'private/products/sku-404', [])
            ->willReturn($this->response);

        DeleteProduct::run($this->client, 'sku-404');
        $this->addToAssertionCount(1);
    }

    public function testRunWithTalerException(): void
    {
        $this->expectException(TalerException::class);

        $this->httpClientWrapper->method('request')->willThrowException(new TalerException('boom'));
        DeleteProduct::run($this->client, 'sku-err');
    }

    public function testRunAsync(): void
    {
        $promise = new Promise();

        $this->response->method('getStatusCode')->willReturn(204);
        $this->stream->method('__toString')->willReturn('');
        $this->response->method('getBody')->willReturn($this->stream);

        $this->httpClientWrapper->expects($this->once())
            ->method('requestAsync')
            ->with('DELETE', 'private/products/sku-2', [])
            ->willReturn($promise);

        $result = DeleteProduct::runAsync($this->client, 'sku-2');
        $promise->resolve($this->response);

        $this->assertNull($result->wait());
    }
}



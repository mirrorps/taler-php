<?php

namespace Taler\Tests\Api\Inventory\Actions;

use GuzzleHttp\Promise\Promise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Taler\Api\Inventory\Actions\UpdateProduct;
use Taler\Api\Inventory\Dto\ProductPatchDetail;
use Taler\Api\Inventory\InventoryClient;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

class UpdateProductTest extends TestCase
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
        $details = new ProductPatchDetail(description: 'Updated', unit: 'kg', price: 'EUR:1', total_stock: 1);

        $this->response->method('getStatusCode')->willReturn(204);
        $this->stream->method('__toString')->willReturn('');
        $this->response->method('getBody')->willReturn($this->stream);

        $headers = ['X-Test' => 'test'];

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('PATCH', 'private/products/sku-1', $headers, $this->anything())
            ->willReturn($this->response);

        UpdateProduct::run($this->client, 'sku-1', $details, $headers);
        $this->addToAssertionCount(1);
    }

    public function testRunWithTalerException(): void
    {
        $this->expectException(TalerException::class);

        $details = new ProductPatchDetail(description: 'Updated', unit: 'kg', price: 'EUR:1', total_stock: 1);

        $this->httpClientWrapper->method('request')->willThrowException(new TalerException('boom'));
        UpdateProduct::run($this->client, 'sku-2', $details);
    }

    public function testRunAsync(): void
    {
        $details = new ProductPatchDetail(description: 'Updated', unit: 'kg', price: 'EUR:1', total_stock: 1);

        $promise = new Promise();

        $this->response->method('getStatusCode')->willReturn(204);
        $this->stream->method('__toString')->willReturn('');
        $this->response->method('getBody')->willReturn($this->stream);

        $this->httpClientWrapper->expects($this->once())
            ->method('requestAsync')
            ->with('PATCH', 'private/products/sku-3', [], $this->anything())
            ->willReturn($promise);

        $result = UpdateProduct::runAsync($this->client, 'sku-3', $details);
        $promise->resolve($this->response);

        $this->assertNull($result->wait());
    }
}



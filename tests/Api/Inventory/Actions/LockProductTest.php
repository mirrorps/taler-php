<?php

namespace Taler\Tests\Api\Inventory\Actions;

use GuzzleHttp\Promise\Promise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Taler\Api\Dto\RelativeTime;
use Taler\Api\Inventory\Actions\LockProduct;
use Taler\Api\Inventory\Dto\LockRequest;
use Taler\Api\Inventory\InventoryClient;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

class LockProductTest extends TestCase
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
        $req = new LockRequest('123e4567-e89b-12d3-a456-426614174000', new RelativeTime(1_000_000), 2);

        $this->response->method('getStatusCode')->willReturn(204);
        $this->stream->method('__toString')->willReturn('');
        $this->response->method('getBody')->willReturn($this->stream);

        $headers = ['X-Test' => 'test'];

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('POST', 'private/products/sku-1/lock', $headers, $this->anything())
            ->willReturn($this->response);

        LockProduct::run($this->client, 'sku-1', $req, $headers);
        $this->addToAssertionCount(1);
    }

    public function testRunWithTalerException(): void
    {
        $this->expectException(TalerException::class);

        $req = new LockRequest('123e4567-e89b-12d3-a456-426614174000', new RelativeTime(1_000_000), 2);

        $this->httpClientWrapper->method('request')->willThrowException(new TalerException('boom'));
        LockProduct::run($this->client, 'sku-1', $req);
    }

    public function testRunAsync(): void
    {
        $req = new LockRequest('123e4567-e89b-12d3-a456-426614174000', new RelativeTime(1_000_000), 2);

        $promise = new Promise();

        $this->response->method('getStatusCode')->willReturn(204);
        $this->stream->method('__toString')->willReturn('');
        $this->response->method('getBody')->willReturn($this->stream);

        $this->httpClientWrapper->expects($this->once())
            ->method('requestAsync')
            ->with('POST', 'private/products/sku-2/lock', [], $this->anything())
            ->willReturn($promise);

        $result = LockProduct::runAsync($this->client, 'sku-2', $req);
        $promise->resolve($this->response);

        $this->assertNull($result->wait());
    }
}



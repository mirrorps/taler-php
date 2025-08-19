<?php

namespace Taler\Tests\Api\Inventory\Actions;

use GuzzleHttp\Promise\Promise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Taler\Api\Inventory\Actions\CreateCategory;
use Taler\Api\Inventory\Dto\CategoryCreateRequest;
use Taler\Api\Inventory\Dto\CategoryCreatedResponse;
use Taler\Api\Inventory\InventoryClient;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

class CreateCategoryTest extends TestCase
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
        $req = new CategoryCreateRequest('Beverages', ['en' => 'Beverages']);

        $expected = [ 'category_id' => 101 ];

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn(json_encode($expected));
        $this->response->method('getBody')->willReturn($this->stream);

        $headers = ['X-Test' => 'test'];

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('POST', 'private/categories', $headers, $this->anything())
            ->willReturn($this->response);

        $result = CreateCategory::run($this->client, $req, $headers);

        $this->assertInstanceOf(CategoryCreatedResponse::class, $result);
        $this->assertSame(101, $result->category_id);
    }

    public function testRunWithTalerException(): void
    {
        $this->expectException(TalerException::class);

        $req = new CategoryCreateRequest('Snacks');

        $this->httpClientWrapper->method('request')->willThrowException(new TalerException('boom'));
        CreateCategory::run($this->client, $req);
    }

    public function testRunAsync(): void
    {
        $req = new CategoryCreateRequest('Beverages');

        $expected = [ 'category_id' => 5 ];

        $promise = new Promise();

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn(json_encode($expected));
        $this->response->method('getBody')->willReturn($this->stream);

        $this->httpClientWrapper->expects($this->once())
            ->method('requestAsync')
            ->with('POST', 'private/categories', [], $this->anything())
            ->willReturn($promise);

        $result = CreateCategory::runAsync($this->client, $req);
        $promise->resolve($this->response);

        $this->assertInstanceOf(CategoryCreatedResponse::class, $result->wait());
        $this->assertSame(5, $result->wait()->category_id);
    }
}



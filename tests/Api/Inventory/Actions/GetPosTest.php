<?php

namespace Taler\Tests\Api\Inventory\Actions;

use GuzzleHttp\Promise\Promise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Taler\Api\Inventory\Actions\GetPos;
use Taler\Api\Inventory\Dto\FullInventoryDetailsResponse;
use Taler\Api\Inventory\InventoryClient;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

class GetPosTest extends TestCase
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
                [
                    'product_serial' => 1,
                    'product_id' => 'p1',
                    'product_name' => 'Name',
                    'categories' => [1],
                    'description' => 'Desc',
                    'description_i18n' => ['en' => 'Desc'],
                    'unit' => 'kg',
                    'price' => 'EUR:1',
                ]
            ],
            'categories' => [
                ['id' => 1, 'name' => 'Drinks']
            ]
        ];

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn(json_encode($expected));
        $this->response->method('getBody')->willReturn($this->stream);

        $headers = ['X-Test' => 'test'];

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('GET', 'private/pos', $headers)
            ->willReturn($this->response);

        $result = GetPos::run($this->client, $headers);

        $this->assertInstanceOf(FullInventoryDetailsResponse::class, $result);
        $this->assertCount(1, $result->products);
        $this->assertCount(1, $result->categories);
    }

    public function testRunWithTalerException(): void
    {
        $this->expectException(TalerException::class);

        $this->httpClientWrapper->method('request')->willThrowException(new TalerException('boom'));
        GetPos::run($this->client);
    }

    public function testRunAsync(): void
    {
        $expected = [
            'products' => [
                [
                    'product_serial' => 1,
                    'product_id' => 'p1',
                    'product_name' => 'Name',
                    'categories' => [1],
                    'description' => 'Desc',
                    'description_i18n' => ['en' => 'Desc'],
                    'unit' => 'kg',
                    'price' => 'EUR:1',
                ]
            ],
            'categories' => [
                ['id' => 1, 'name' => 'Drinks']
            ]
        ];

        $promise = new Promise();

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn(json_encode($expected));
        $this->response->method('getBody')->willReturn($this->stream);

        $this->httpClientWrapper->expects($this->once())
            ->method('requestAsync')
            ->with('GET', 'private/pos', [])
            ->willReturn($promise);

        $result = GetPos::runAsync($this->client);
        $promise->resolve($this->response);

        $this->assertInstanceOf(FullInventoryDetailsResponse::class, $result->wait());
        $this->assertSame('p1', $result->wait()->products[0]->product_id);
    }
}



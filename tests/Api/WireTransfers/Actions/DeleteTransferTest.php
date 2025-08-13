<?php

namespace Taler\Tests\Api\WireTransfers\Actions;

use GuzzleHttp\Promise\Promise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Taler\Api\WireTransfers\Actions\DeleteTransfer;
use Taler\Api\WireTransfers\WireTransfersClient;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

class DeleteTransferTest extends TestCase
{
    private WireTransfersClient $client;
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

        $this->client = new WireTransfersClient($this->taler, $this->httpClientWrapper);
    }

    public function testRunSuccess(): void
    {
        $this->response->method('getStatusCode')->willReturn(204);
        $this->stream->method('__toString')->willReturn('');
        $this->response->method('getBody')->willReturn($this->stream);

        $tid = '123';
        $headers = ['X-Test' => 'test'];

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('DELETE', "private/transfers/{$tid}", $headers)
            ->willReturn($this->response);

        DeleteTransfer::run($this->client, $tid, $headers);
    }

    public function testRunWithTalerException(): void
    {
        $this->expectException(TalerException::class);
        $this->httpClientWrapper->method('request')->willThrowException(new TalerException('boom'));
        DeleteTransfer::run($this->client, '123');
    }

    public function testRunAsync(): void
    {
        $promise = new Promise();
        $this->response->method('getStatusCode')->willReturn(204);
        $this->stream->method('__toString')->willReturn('');
        $this->response->method('getBody')->willReturn($this->stream);

        $this->httpClientWrapper->expects($this->once())
            ->method('requestAsync')
            ->with('DELETE', 'private/transfers/123', [])
            ->willReturn($promise);

        $result = DeleteTransfer::runAsync($this->client, '123');
        $promise->resolve($this->response);

        $this->assertNull($result->wait());
    }
}



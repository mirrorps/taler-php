<?php

namespace Taler\Tests\Api\WireTransfers\Actions;

use GuzzleHttp\Promise\Promise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Taler\Api\WireTransfers\Actions\GetTransfers;
use Taler\Api\WireTransfers\Dto\GetTransfersRequest;
use Taler\Api\WireTransfers\Dto\TransfersList;
use Taler\Api\WireTransfers\WireTransfersClient;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

class GetTransfersTest extends TestCase
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
        $expectedData = [
            'transfers' => [
                [
                    'credit_amount' => 'EUR:10.00',
                    'wtid' => 'WTID1',
                    'payto_uri' => 'payto://iban/DE00',
                    'exchange_url' => 'https://ex1.example.com',
                    'transfer_serial_id' => 11,
                    'execution_time' => ['t_s' => 1700000000],
                    'verified' => true,
                    'confirmed' => false,
                    'expected' => true,
                ]
            ]
        ];

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn(json_encode($expectedData));
        $this->response->method('getBody')->willReturn($this->stream);

        $req = new GetTransfersRequest(limit: 5);
        $headers = ['X-Test' => 'test'];

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('GET', 'private/transfers?' . http_build_query($req->toArray()), $headers)
            ->willReturn($this->response);

        $result = GetTransfers::run($this->client, $req, $headers);

        $this->assertInstanceOf(TransfersList::class, $result);
        $this->assertCount(1, $result->transfers);
        $this->assertSame('WTID1', $result->transfers[0]->wtid);
    }

    public function testRunNoParams(): void
    {
        $expectedData = ['transfers' => []];
        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn(json_encode($expectedData));
        $this->response->method('getBody')->willReturn($this->stream);

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('GET', 'private/transfers', [])
            ->willReturn($this->response);

        $result = GetTransfers::run($this->client);
        $this->assertInstanceOf(TransfersList::class, $result);
        $this->assertCount(0, $result->transfers);
    }

    public function testRunWithException(): void
    {
        $this->expectException(TalerException::class);
        $this->httpClientWrapper->method('request')->willThrowException(new TalerException('boom'));
        GetTransfers::run($this->client);
    }

    public function testRunAsync(): void
    {
        $expectedData = ['transfers' => []];
        $promise = new Promise();

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn(json_encode($expectedData));
        $this->response->method('getBody')->willReturn($this->stream);

        $this->httpClientWrapper->expects($this->once())
            ->method('requestAsync')
            ->with('GET', 'private/transfers', [])
            ->willReturn($promise);

        $result = GetTransfers::runAsync($this->client);
        $promise->resolve($this->response);

        $this->assertInstanceOf(TransfersList::class, $result->wait());
    }
}



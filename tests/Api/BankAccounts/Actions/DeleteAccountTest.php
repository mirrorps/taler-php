<?php

namespace Taler\Tests\Api\BankAccounts\Actions;

use GuzzleHttp\Promise\Promise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Taler\Api\BankAccounts\Actions\DeleteAccount;
use Taler\Api\BankAccounts\BankAccountClient;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

class DeleteAccountTest extends TestCase
{
    private BankAccountClient $client;
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

        $this->client = new BankAccountClient($this->taler, $this->httpClientWrapper);
    }

    public function testRunSuccess(): void
    {
        $hWire = 'hw';

        $this->response->method('getStatusCode')->willReturn(204);
        $this->stream->method('__toString')->willReturn('');
        $this->response->method('getBody')->willReturn($this->stream);

        $headers = [];

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('DELETE', "private/accounts/{$hWire}", $headers)
            ->willReturn($this->response);

        DeleteAccount::run($this->client, $hWire);
        $this->addToAssertionCount(1); // reached here without exception
    }

    public function testRunWithTalerException(): void
    {
        $hWire = 'hw';

        $this->httpClientWrapper->method('request')
            ->willThrowException(new TalerException('Test exception'));

        $this->expectException(TalerException::class);
        DeleteAccount::run($this->client, $hWire);
    }

    public function testRunWithGenericException(): void
    {
        $hWire = 'hw';

        $this->httpClientWrapper->method('request')
            ->willThrowException(new \RuntimeException('Test generic exception'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Taler delete bank account request failed'));

        $this->expectException(\RuntimeException::class);
        DeleteAccount::run($this->client, $hWire);
    }

    public function testRunAsync(): void
    {
        $hWire = 'hw';

        $promise = new Promise();

        $this->response->method('getStatusCode')->willReturn(204);
        $this->stream->method('__toString')->willReturn('');
        $this->response->method('getBody')->willReturn($this->stream);

        $headers = [];

        $this->httpClientWrapper->expects($this->once())
            ->method('requestAsync')
            ->with('DELETE', "private/accounts/{$hWire}", $headers)
            ->willReturn($promise);

        $result = DeleteAccount::runAsync($this->client, $hWire);
        $promise->resolve($this->response);

        $this->assertNull($result->wait());
    }
}



<?php

namespace Taler\Tests\Api\BankAccounts\Actions;

use GuzzleHttp\Promise\Promise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Taler\Api\BankAccounts\Actions\GetAccount;
use Taler\Api\BankAccounts\BankAccountClient;
use Taler\Api\BankAccounts\Dto\BankAccountDetail;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

class GetAccountTest extends TestCase
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
        $expected = [
            'payto_uri' => 'payto://iban/DE1',
            'h_wire' => $hWire,
            'salt' => 'salt123',
            'active' => true,
            'credit_facade_url' => 'https://facade'
        ];

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn(json_encode($expected));
        $this->response->method('getBody')->willReturn($this->stream);

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('GET', "private/accounts/$hWire", [])
            ->willReturn($this->response);

        $result = GetAccount::run($this->client, $hWire);
        $this->assertInstanceOf(BankAccountDetail::class, $result);
        $this->assertSame('payto://iban/DE1', $result->payto_uri);
        $this->assertSame($hWire, $result->h_wire);
        $this->assertTrue($result->active);
    }

    public function testRunWithTalerException(): void
    {
        $this->httpClientWrapper->method('request')
            ->willThrowException(new TalerException('Test exception'));

        $this->expectException(TalerException::class);
        GetAccount::run($this->client, 'hw');
    }

    public function testRunWithGenericException(): void
    {
        $this->httpClientWrapper->method('request')
            ->willThrowException(new \RuntimeException('Test generic exception'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Taler get bank account request failed'));

        $this->expectException(\RuntimeException::class);
        GetAccount::run($this->client, 'hw');
    }

    public function testRunAsync(): void
    {
        $hWire = 'hw';
        $expected = [
            'payto_uri' => 'payto://iban/DE1',
            'h_wire' => $hWire,
            'salt' => 'salt123',
            'active' => true
        ];

        $promise = new Promise();

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn(json_encode($expected));
        $this->response->method('getBody')->willReturn($this->stream);

        $this->httpClientWrapper->expects($this->once())
            ->method('requestAsync')
            ->with('GET', "private/accounts/$hWire", [])
            ->willReturn($promise);

        $result = GetAccount::runAsync($this->client, $hWire);
        $promise->resolve($this->response);

        $this->assertInstanceOf(BankAccountDetail::class, $result->wait());
        $this->assertSame($hWire, $result->wait()->h_wire);
    }
}



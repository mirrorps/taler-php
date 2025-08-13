<?php

namespace Taler\Tests\Api\BankAccounts\Actions;

use GuzzleHttp\Promise\Promise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Taler\Api\BankAccounts\Actions\GetAccounts;
use Taler\Api\BankAccounts\BankAccountClient;
use Taler\Api\BankAccounts\Dto\AccountsSummaryResponse;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

class GetAccountsTest extends TestCase
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
        $expectedData = [
            'accounts' => [
                ['payto_uri' => 'payto://iban/DE1', 'h_wire' => 'h1', 'active' => true],
                ['payto_uri' => 'payto://iban/DE2', 'h_wire' => 'h2', 'active' => false],
            ]
        ];

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn(json_encode($expectedData));
        $this->response->method('getBody')->willReturn($this->stream);

        $headers = [];

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('GET', 'private/accounts', $headers)
            ->willReturn($this->response);

        $result = GetAccounts::run($this->client);

        $this->assertInstanceOf(AccountsSummaryResponse::class, $result);
        $this->assertCount(2, $result->accounts);
        $this->assertSame('payto://iban/DE1', $result->accounts[0]->payto_uri);
    }

    public function testRunWithTalerException(): void
    {
        $this->httpClientWrapper->method('request')
            ->willThrowException(new TalerException('Test exception'));

        $this->expectException(TalerException::class);
        $this->expectExceptionMessage('Test exception');

        GetAccounts::run($this->client);
    }

    public function testRunWithGenericException(): void
    {
        $this->httpClientWrapper->method('request')
            ->willThrowException(new \RuntimeException('Test generic exception'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Taler get bank accounts request failed'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Test generic exception');

        GetAccounts::run($this->client);
    }

    public function testRunAsync(): void
    {
        $expectedData = [
            'accounts' => [
                ['payto_uri' => 'payto://iban/DE1', 'h_wire' => 'h1', 'active' => true]
            ]
        ];

        $promise = new Promise();

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn(json_encode($expectedData));
        $this->response->method('getBody')->willReturn($this->stream);

        $headers = [];

        $this->httpClientWrapper->expects($this->once())
            ->method('requestAsync')
            ->with('GET', 'private/accounts', $headers)
            ->willReturn($promise);

        $result = GetAccounts::runAsync($this->client);
        $promise->resolve($this->response);

        $this->assertInstanceOf(AccountsSummaryResponse::class, $result->wait());
        $this->assertCount(1, $result->wait()->accounts);
    }
}



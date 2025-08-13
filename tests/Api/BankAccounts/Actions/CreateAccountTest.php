<?php

namespace Taler\Tests\Api\BankAccounts\Actions;

use GuzzleHttp\Promise\Promise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Taler\Api\BankAccounts\Actions\CreateAccount;
use Taler\Api\BankAccounts\BankAccountClient;
use Taler\Api\BankAccounts\Dto\AccountAddDetails;
use Taler\Api\BankAccounts\Dto\AccountAddResponse;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

class CreateAccountTest extends TestCase
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
        $details = new AccountAddDetails('payto://iban/DE123');

        $expectedData = [
            'h_wire' => 'hw',
            'salt' => 's'
        ];

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn(json_encode($expectedData));
        $this->response->method('getBody')->willReturn($this->stream);

        $headers = [];
        $requestData = json_encode($details);

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('POST', 'private/accounts', $headers, $requestData)
            ->willReturn($this->response);

        $result = CreateAccount::run($this->client, $details);

        $this->assertInstanceOf(AccountAddResponse::class, $result);
        $this->assertEquals('hw', $result->h_wire);
        $this->assertEquals('s', $result->salt);
    }

    public function testRunWithTalerException(): void
    {
        $details = new AccountAddDetails('payto://iban/DE123');

        $this->httpClientWrapper->method('request')
            ->willThrowException(new TalerException('Test exception'));

        $this->expectException(TalerException::class);
        $this->expectExceptionMessage('Test exception');

        CreateAccount::run($this->client, $details);
    }

    public function testRunWithGenericException(): void
    {
        $details = new AccountAddDetails('payto://iban/DE123');

        $this->httpClientWrapper->method('request')
            ->willThrowException(new \RuntimeException('Test generic exception'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Taler create bank account request failed'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Test generic exception');

        CreateAccount::run($this->client, $details);
    }

    public function testRunAsync(): void
    {
        $details = new AccountAddDetails('payto://iban/DE123');

        $expectedData = [
            'h_wire' => 'hw',
            'salt' => 's'
        ];

        $promise = new Promise();

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn(json_encode($expectedData));
        $this->response->method('getBody')->willReturn($this->stream);

        $headers = [];
        $requestData = json_encode($details);

        $this->httpClientWrapper->expects($this->once())
            ->method('requestAsync')
            ->with('POST', 'private/accounts', $headers, $requestData)
            ->willReturn($promise);

        $result = CreateAccount::runAsync($this->client, $details);
        $promise->resolve($this->response);

        $this->assertInstanceOf(AccountAddResponse::class, $result->wait());
        $this->assertEquals('hw', $result->wait()->h_wire);
        $this->assertEquals('s', $result->wait()->salt);
    }
}



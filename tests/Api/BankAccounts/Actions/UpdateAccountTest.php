<?php

namespace Taler\Tests\Api\BankAccounts\Actions;

use GuzzleHttp\Promise\Promise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Taler\Api\BankAccounts\Actions\UpdateAccount;
use Taler\Api\BankAccounts\BankAccountClient;
use Taler\Api\BankAccounts\Dto\AccountPatchDetails;
use Taler\Api\BankAccounts\Dto\BasicAuthFacadeCredentials;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

class UpdateAccountTest extends TestCase
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
        $details = new AccountPatchDetails('https://facade', new BasicAuthFacadeCredentials('u', 'p'));

        $this->response->method('getStatusCode')->willReturn(204);
        $this->stream->method('__toString')->willReturn('');
        $this->response->method('getBody')->willReturn($this->stream);

        $headers = [];
        $requestData = json_encode($details);

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('PATCH', "private/accounts/{$hWire}", $headers, $requestData)
            ->willReturn($this->response);

        UpdateAccount::run($this->client, $hWire, $details);
        $this->addToAssertionCount(1); // reached here without exception
    }

    public function testRunWithTalerException(): void
    {
        $hWire = 'hw';
        $details = new AccountPatchDetails('https://facade', new BasicAuthFacadeCredentials('u', 'p'));

        $this->httpClientWrapper->method('request')
            ->willThrowException(new TalerException('Test exception'));

        $this->expectException(TalerException::class);
        UpdateAccount::run($this->client, $hWire, $details);
    }

    public function testRunWithGenericException(): void
    {
        $hWire = 'hw';
        $details = new AccountPatchDetails('https://facade', new BasicAuthFacadeCredentials('u', 'p'));

        $this->httpClientWrapper->method('request')
            ->willThrowException(new \RuntimeException('Test generic exception'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Taler update bank account request failed'));

        $this->expectException(\RuntimeException::class);
        UpdateAccount::run($this->client, $hWire, $details);
    }

    public function testRunAsync(): void
    {
        $hWire = 'hw';
        $details = new AccountPatchDetails('https://facade', new BasicAuthFacadeCredentials('u', 'p'));

        $promise = new Promise();

        $this->response->method('getStatusCode')->willReturn(204);
        $this->stream->method('__toString')->willReturn('');
        $this->response->method('getBody')->willReturn($this->stream);

        $headers = [];
        $requestData = json_encode($details);

        $this->httpClientWrapper->expects($this->once())
            ->method('requestAsync')
            ->with('PATCH', "private/accounts/{$hWire}", $headers, $requestData)
            ->willReturn($promise);

        $result = UpdateAccount::runAsync($this->client, $hWire, $details);
        $promise->resolve($this->response);

        $this->assertNull($result->wait());
    }
}




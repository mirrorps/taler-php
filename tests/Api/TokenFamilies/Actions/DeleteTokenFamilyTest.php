<?php

namespace Taler\Tests\Api\TokenFamilies\Actions;

use GuzzleHttp\Promise\Promise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Taler\Api\TokenFamilies\Actions\DeleteTokenFamily;
use Taler\Api\TokenFamilies\TokenFamiliesClient;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

class DeleteTokenFamilyTest extends TestCase
{
    private TokenFamiliesClient $client;
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

        $this->client = new TokenFamiliesClient($this->taler, $this->httpClientWrapper);
    }

    public function testRunSuccess(): void
    {
        $slug = 'family-01';

        $this->response->method('getStatusCode')->willReturn(204);
        $this->stream->method('__toString')->willReturn('');
        $this->response->method('getBody')->willReturn($this->stream);

        $headers = ['X-Test' => 'test'];

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('DELETE', "private/tokenfamilies/{$slug}", $headers)
            ->willReturn($this->response);

        DeleteTokenFamily::run($this->client, $slug, $headers);
        $this->addToAssertionCount(1);
    }

    public function testRunWithTalerException(): void
    {
        $this->expectException(TalerException::class);
        $slug = 'family-01';
        $this->httpClientWrapper->method('request')->willThrowException(new TalerException('boom'));
        DeleteTokenFamily::run($this->client, $slug);
    }

    public function testRunAsync(): void
    {
        $slug = 'family-01';
        $promise = new Promise();

        $this->response->method('getStatusCode')->willReturn(204);
        $this->stream->method('__toString')->willReturn('');
        $this->response->method('getBody')->willReturn($this->stream);

        $this->httpClientWrapper->expects($this->once())
            ->method('requestAsync')
            ->with('DELETE', "private/tokenfamilies/{$slug}", [])
            ->willReturn($promise);

        $result = DeleteTokenFamily::runAsync($this->client, $slug);
        $promise->resolve($this->response);

        $this->assertNull($result->wait());
    }
}



<?php

namespace Taler\Tests\Api\TokenFamilies\Actions;

use GuzzleHttp\Promise\Promise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Taler\Api\TokenFamilies\Actions\GetTokenFamilies;
use Taler\Api\TokenFamilies\Dto\TokenFamiliesList;
use Taler\Api\TokenFamilies\TokenFamiliesClient;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

class GetTokenFamiliesTest extends TestCase
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
        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn(json_encode([
            'token_families' => [
                [
                    'slug' => 'fam-1',
                    'name' => 'Fam 1',
                    'valid_after' => ['t_s' => 1700000000],
                    'valid_before' => ['t_s' => 1800000000],
                    'kind' => 'discount',
                ],
            ],
        ], JSON_THROW_ON_ERROR));
        $this->response->method('getBody')->willReturn($this->stream);

        $headers = ['X-Test' => 'test'];

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('GET', 'private/tokenfamilies', $headers)
            ->willReturn($this->response);

        $result = GetTokenFamilies::run($this->client, $headers);
        $this->assertInstanceOf(TokenFamiliesList::class, $result);
        $this->assertCount(1, $result->token_families);
    }

    public function testRunWithTalerException(): void
    {
        $this->expectException(TalerException::class);
        $this->httpClientWrapper->method('request')->willThrowException(new TalerException('boom'));
        GetTokenFamilies::run($this->client);
    }

    public function testRunAsync(): void
    {
        $promise = new Promise();

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn(json_encode([
            'token_families' => [],
        ], JSON_THROW_ON_ERROR));
        $this->response->method('getBody')->willReturn($this->stream);

        $this->httpClientWrapper->expects($this->once())
            ->method('requestAsync')
            ->with('GET', 'private/tokenfamilies', [])
            ->willReturn($promise);

        $result = GetTokenFamilies::runAsync($this->client);
        $promise->resolve($this->response);

        $this->assertInstanceOf(TokenFamiliesList::class, $result->wait());
    }
}



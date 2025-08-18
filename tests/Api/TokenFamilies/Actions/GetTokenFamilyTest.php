<?php

namespace Taler\Tests\Api\TokenFamilies\Actions;

use GuzzleHttp\Promise\Promise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Taler\Api\TokenFamilies\Actions\GetTokenFamily;
use Taler\Api\TokenFamilies\Dto\TokenFamilyDetails;
use Taler\Api\TokenFamilies\TokenFamiliesClient;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

class GetTokenFamilyTest extends TestCase
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

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn(json_encode([
            'slug' => $slug,
            'name' => 'A',
            'description' => 'B',
            'valid_after' => ['t_s' => 1700000000],
            'valid_before' => ['t_s' => 1800000000],
            'duration' => ['d_us' => 60_000_000],
            'validity_granularity' => ['d_us' => 60_000_000],
            'start_offset' => ['d_us' => 0],
            'kind' => 'discount',
            'issued' => 10,
            'used' => 2,
        ], JSON_THROW_ON_ERROR));
        $this->response->method('getBody')->willReturn($this->stream);

        $headers = ['X-Test' => 'test'];

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('GET', "private/tokenfamilies/{$slug}", $headers)
            ->willReturn($this->response);

        $result = GetTokenFamily::run($this->client, $slug, $headers);
        $this->assertInstanceOf(TokenFamilyDetails::class, $result);
        $this->assertSame('A', $result->name);
    }

    public function testRunWithTalerException(): void
    {
        $this->expectException(TalerException::class);
        $slug = 'family-01';
        $this->httpClientWrapper->method('request')->willThrowException(new TalerException('boom'));
        GetTokenFamily::run($this->client, $slug);
    }

    public function testRunAsync(): void
    {
        $slug = 'family-01';

        $promise = new Promise();

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn(json_encode([
            'slug' => $slug,
            'name' => 'A',
            'description' => 'B',
            'valid_after' => ['t_s' => 1700000000],
            'valid_before' => ['t_s' => 1800000000],
            'duration' => ['d_us' => 60_000_000],
            'validity_granularity' => ['d_us' => 60_000_000],
            'start_offset' => ['d_us' => 0],
            'kind' => 'discount',
            'issued' => 10,
            'used' => 2,
        ], JSON_THROW_ON_ERROR));
        $this->response->method('getBody')->willReturn($this->stream);

        $this->httpClientWrapper->expects($this->once())
            ->method('requestAsync')
            ->with('GET', "private/tokenfamilies/{$slug}", [])
            ->willReturn($promise);

        $result = GetTokenFamily::runAsync($this->client, $slug);
        $promise->resolve($this->response);

        $this->assertInstanceOf(TokenFamilyDetails::class, $result->wait());
    }
}



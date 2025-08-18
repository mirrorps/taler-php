<?php

namespace Taler\Tests\Api\TokenFamilies\Actions;

use GuzzleHttp\Promise\Promise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Taler\Api\Dto\RelativeTime;
use Taler\Api\Dto\Timestamp;
use Taler\Api\TokenFamilies\Actions\CreateTokenFamily;
use Taler\Api\TokenFamilies\Dto\TokenFamilyCreateRequest;
use Taler\Api\TokenFamilies\TokenFamiliesClient;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

class CreateTokenFamilyTest extends TestCase
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

    private function makeRequest(): TokenFamilyCreateRequest
    {
        return new TokenFamilyCreateRequest(
            slug: 'family-01',
            name: 'My Family',
            description: 'Desc',
            description_i18n: null,
            extra_data: null,
            valid_after: new Timestamp(1700000000),
            valid_before: new Timestamp(1800000000),
            duration: new RelativeTime(1000000),
            validity_granularity: new RelativeTime(60000000),
            start_offset: new RelativeTime(0),
            kind: 'discount'
        );
    }

    public function testRunSuccess(): void
    {
        $details = $this->makeRequest();

        $this->response->method('getStatusCode')->willReturn(204);
        $this->stream->method('__toString')->willReturn('');
        $this->response->method('getBody')->willReturn($this->stream);

        $headers = ['X-Test' => 'test'];

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('POST', 'private/tokenfamilies', $headers, $this->anything())
            ->willReturn($this->response);

        CreateTokenFamily::run($this->client, $details, $headers);
        $this->addToAssertionCount(1);
    }

    public function testRunWithTalerException(): void
    {
        $this->expectException(TalerException::class);

        $details = $this->makeRequest();

        $this->httpClientWrapper->method('request')->willThrowException(new TalerException('boom'));
        CreateTokenFamily::run($this->client, $details);
    }

    public function testRunAsync(): void
    {
        $details = $this->makeRequest();

        $promise = new Promise();

        $this->response->method('getStatusCode')->willReturn(204);
        $this->stream->method('__toString')->willReturn('');
        $this->response->method('getBody')->willReturn($this->stream);

        $this->httpClientWrapper->expects($this->once())
            ->method('requestAsync')
            ->with('POST', 'private/tokenfamilies', [], $this->anything())
            ->willReturn($promise);

        $result = CreateTokenFamily::runAsync($this->client, $details);
        $promise->resolve($this->response);

        $this->assertNull($result->wait());
    }
}



<?php

namespace Taler\Tests\Api\TokenFamilies\Actions;

use GuzzleHttp\Promise\Promise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Taler\Api\Dto\Timestamp;
use Taler\Api\TokenFamilies\Actions\UpdateTokenFamily;
use Taler\Api\TokenFamilies\Dto\TokenFamilyDetails;
use Taler\Api\TokenFamilies\Dto\TokenFamilyUpdateRequest;
use Taler\Api\TokenFamilies\TokenFamiliesClient;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

class UpdateTokenFamilyTest extends TestCase
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

    private function makeRequest(): TokenFamilyUpdateRequest
    {
        return new TokenFamilyUpdateRequest(
            name: 'Updated Name',
            description: 'Updated Desc',
            description_i18n: null,
            extra_data: null,
            valid_after: new Timestamp(1700000000),
            valid_before: new Timestamp(1800000000),
        );
    }

    public function testRunSuccess(): void
    {
        $slug = 'family-01';
        $details = $this->makeRequest();

        $this->response->method('getStatusCode')->willReturn(204);
        $this->stream->method('__toString')->willReturn(json_encode([
            'slug' => $slug,
            'name' => 'Updated Name',
            'description' => 'Updated Desc',
            'valid_after' => ['t_s' => 1700000000],
            'valid_before' => ['t_s' => 1800000000],
            'duration' => ['d_us' => 60_000_000],
            'validity_granularity' => ['d_us' => 60_000_000],
            'start_offset' => ['d_us' => 0],
            'kind' => 'discount',
            'issued' => 10,
            'used' => 5,
        ], JSON_THROW_ON_ERROR));
        $this->response->method('getBody')->willReturn($this->stream);

        $headers = ['X-Test' => 'test'];

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('PATCH', "private/tokenfamilies/{$slug}", $headers, $this->anything())
            ->willReturn($this->response);

        $result = UpdateTokenFamily::run($this->client, $slug, $details, $headers);
        $this->assertInstanceOf(TokenFamilyDetails::class, $result);
        $this->assertSame('Updated Name', $result->name);
        $this->assertSame(10, $result->issued);
        $this->assertSame(5, $result->used);
    }

    public function testRunWithTalerException(): void
    {
        $this->expectException(TalerException::class);
        $slug = 'family-01';
        $details = $this->makeRequest();
        $this->httpClientWrapper->method('request')->willThrowException(new TalerException('boom'));
        UpdateTokenFamily::run($this->client, $slug, $details);
    }

    public function testRunAsync(): void
    {
        $slug = 'family-01';
        $details = $this->makeRequest();

        $promise = new Promise();

        $this->response->method('getStatusCode')->willReturn(204);
        $this->stream->method('__toString')->willReturn(json_encode([
            'slug' => $slug,
            'name' => 'Updated Name',
            'description' => 'Updated Desc',
            'valid_after' => ['t_s' => 1700000000],
            'valid_before' => ['t_s' => 1800000000],
            'duration' => ['d_us' => 60_000_000],
            'validity_granularity' => ['d_us' => 60_000_000],
            'start_offset' => ['d_us' => 0],
            'kind' => 'discount',
            'issued' => 10,
            'used' => 5,
        ], JSON_THROW_ON_ERROR));
        $this->response->method('getBody')->willReturn($this->stream);

        $this->httpClientWrapper->expects($this->once())
            ->method('requestAsync')
            ->with('PATCH', "private/tokenfamilies/{$slug}", [], $this->anything())
            ->willReturn($promise);

        $result = UpdateTokenFamily::runAsync($this->client, $slug, $details);
        $promise->resolve($this->response);

        $this->assertInstanceOf(TokenFamilyDetails::class, $result->wait());
    }
}



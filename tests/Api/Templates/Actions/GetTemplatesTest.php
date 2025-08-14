<?php

namespace Taler\Tests\Api\Templates\Actions;

use GuzzleHttp\Promise\Promise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Taler\Api\Templates\Actions\GetTemplates;
use Taler\Api\Templates\Dto\TemplatesSummaryResponse;
use Taler\Api\Templates\TemplatesClient;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

class GetTemplatesTest extends TestCase
{
    private TemplatesClient $client;
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

        $this->client = new TemplatesClient($this->taler, $this->httpClientWrapper);
    }

    public function testRunSuccess(): void
    {
        $payload = [
            'templates' => [
                ['template_id' => 't1', 'template_description' => 'First'],
                ['template_id' => 't2', 'template_description' => 'Second'],
            ],
        ];

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn(json_encode($payload));
        $this->response->method('getBody')->willReturn($this->stream);

        $headers = ['X-Test' => 'test'];

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('GET', 'private/templates', $headers)
            ->willReturn($this->response);

        $result = GetTemplates::run($this->client, $headers);

        $this->assertInstanceOf(TemplatesSummaryResponse::class, $result);
        $this->assertCount(2, $result->templates);
        $this->assertSame('t1', $result->templates[0]->template_id);
        $this->assertSame('Second', $result->templates[1]->template_description);
    }

    public function testRunWithTalerException(): void
    {
        $this->expectException(TalerException::class);

        $this->httpClientWrapper->method('request')->willThrowException(new TalerException('boom'));
        GetTemplates::run($this->client);
    }

    public function testRunAsync(): void
    {
        $payload = [
            'templates' => [
                ['template_id' => 't1', 'template_description' => 'First'],
            ],
        ];

        $promise = new Promise();

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn(json_encode($payload));
        $this->response->method('getBody')->willReturn($this->stream);

        $this->httpClientWrapper->expects($this->once())
            ->method('requestAsync')
            ->with('GET', 'private/templates', [])
            ->willReturn($promise);

        $result = GetTemplates::runAsync($this->client);
        $promise->resolve($this->response);

        $resolved = $result->wait();
        $this->assertInstanceOf(TemplatesSummaryResponse::class, $resolved);
        $this->assertCount(1, $resolved->templates);
        $this->assertSame('t1', $resolved->templates[0]->template_id);
    }
}



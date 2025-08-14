<?php

namespace Taler\Tests\Api\Templates\Actions;

use GuzzleHttp\Promise\Promise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Taler\Api\Templates\Actions\DeleteTemplate;
use Taler\Api\Templates\TemplatesClient;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

class DeleteTemplateTest extends TestCase
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
        $templateId = 'tpl-1';

        $this->response->method('getStatusCode')->willReturn(204);
        $this->stream->method('__toString')->willReturn('');
        $this->response->method('getBody')->willReturn($this->stream);

        $headers = [];

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('DELETE', "private/templates/{$templateId}", $headers)
            ->willReturn($this->response);

        DeleteTemplate::run($this->client, $templateId);
        $this->addToAssertionCount(1);
    }

    public function testRunWithTalerException(): void
    {
        $templateId = 'tpl-1';

        $this->httpClientWrapper->method('request')
            ->willThrowException(new TalerException('Test exception'));

        $this->expectException(TalerException::class);
        DeleteTemplate::run($this->client, $templateId);
    }

    public function testRunWithGenericException(): void
    {
        $templateId = 'tpl-1';

        $this->httpClientWrapper->method('request')
            ->willThrowException(new \RuntimeException('Test generic exception'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Taler delete template request failed'));

        $this->expectException(\RuntimeException::class);
        DeleteTemplate::run($this->client, $templateId);
    }

    public function testRunAsync(): void
    {
        $templateId = 'tpl-1';

        $promise = new Promise();

        $this->response->method('getStatusCode')->willReturn(204);
        $this->stream->method('__toString')->willReturn('');
        $this->response->method('getBody')->willReturn($this->stream);

        $headers = [];

        $this->httpClientWrapper->expects($this->once())
            ->method('requestAsync')
            ->with('DELETE', "private/templates/{$templateId}", $headers)
            ->willReturn($promise);

        $result = DeleteTemplate::runAsync($this->client, $templateId);
        $promise->resolve($this->response);

        $this->assertNull($result->wait());
    }
}




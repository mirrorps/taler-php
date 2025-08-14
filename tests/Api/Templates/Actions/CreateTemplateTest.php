<?php

namespace Taler\Tests\Api\Templates\Actions;

use GuzzleHttp\Promise\Promise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Taler\Api\Templates\Actions\CreateTemplate;
use Taler\Api\Templates\Dto\TemplateAddDetails;
use Taler\Api\Templates\Dto\TemplateContractDetails;
use Taler\Api\Templates\TemplatesClient;
use Taler\Api\Dto\RelativeTime;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

class CreateTemplateTest extends TestCase
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
        $details = new TemplateAddDetails(
            template_id: 'tpl-1',
            template_description: 'Invoice template',
            template_contract: new TemplateContractDetails(
                minimum_age: 18,
                pay_duration: new RelativeTime(3600),
                summary: 'Service fee',
                currency: 'EUR',
                amount: 'EUR:10.00',
            ),
            otp_id: 'otp-1',
            editable_defaults: ['summary' => 'Editable']
        );

        $this->response->method('getStatusCode')->willReturn(204);
        $this->stream->method('__toString')->willReturn('');
        $this->response->method('getBody')->willReturn($this->stream);

        $headers = ['X-Test' => 'test'];

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('POST', 'private/templates', $headers, $this->anything())
            ->willReturn($this->response);

        CreateTemplate::run($this->client, $details, $headers);
        $this->addToAssertionCount(1);
    }

    public function testRunWithTalerException(): void
    {
        $this->expectException(TalerException::class);

        $details = new TemplateAddDetails(
            template_id: 'tpl-1',
            template_description: 'Invoice template',
            template_contract: new TemplateContractDetails(
                minimum_age: 18,
                pay_duration: new RelativeTime(3600)
            ),
        );

        $this->httpClientWrapper->method('request')->willThrowException(new TalerException('boom'));
        CreateTemplate::run($this->client, $details);
    }

    public function testRunAsync(): void
    {
        $details = new TemplateAddDetails(
            template_id: 'tpl-1',
            template_description: 'Invoice template',
            template_contract: new TemplateContractDetails(
                minimum_age: 18,
                pay_duration: new RelativeTime(3600),
                summary: 'Service fee',
                currency: 'EUR',
                amount: 'EUR:10.00',
            )
        );

        $promise = new Promise();

        $this->response->method('getStatusCode')->willReturn(204);
        $this->stream->method('__toString')->willReturn('');
        $this->response->method('getBody')->willReturn($this->stream);

        $this->httpClientWrapper->expects($this->once())
            ->method('requestAsync')
            ->with('POST', 'private/templates', [], $this->anything())
            ->willReturn($promise);

        $result = CreateTemplate::runAsync($this->client, $details);
        $promise->resolve($this->response);

        $this->assertNull($result->wait());
    }
}



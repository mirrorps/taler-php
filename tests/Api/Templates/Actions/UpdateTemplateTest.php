<?php

namespace Taler\Tests\Api\Templates\Actions;

use GuzzleHttp\Promise\Promise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Taler\Api\Templates\Actions\UpdateTemplate;
use Taler\Api\Templates\Dto\TemplateContractDetails;
use Taler\Api\Templates\Dto\TemplatePatchDetails;
use Taler\Api\Templates\TemplatesClient;
use Taler\Api\Dto\RelativeTime;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

class UpdateTemplateTest extends TestCase
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
        $details = new TemplatePatchDetails(
            template_description: 'Updated description',
            template_contract: new TemplateContractDetails(
                minimum_age: 18,
                pay_duration: new RelativeTime(3600),
                summary: 'Updated summary',
                currency: 'EUR',
                amount: 'EUR:15.00',
            ),
            otp_id: 'otp-2',
            editable_defaults: ['summary' => 'Editable']
        );

        $this->response->method('getStatusCode')->willReturn(204);
        $this->stream->method('__toString')->willReturn('');
        $this->response->method('getBody')->willReturn($this->stream);

        $headers = ['X-Test' => 'test'];

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('PATCH', "private/templates/{$templateId}", $headers, $this->anything())
            ->willReturn($this->response);

        UpdateTemplate::run($this->client, $templateId, $details, $headers);
        $this->addToAssertionCount(1);
    }

    public function testRunWithTalerException(): void
    {
        $this->expectException(TalerException::class);

        $templateId = 'tpl-1';
        $details = new TemplatePatchDetails(
            template_description: 'desc',
            template_contract: new TemplateContractDetails(
                minimum_age: 18,
                pay_duration: new RelativeTime(3600)
            )
        );

        $this->httpClientWrapper->method('request')->willThrowException(new TalerException('boom'));
        UpdateTemplate::run($this->client, $templateId, $details);
    }

    public function testRunAsync(): void
    {
        $templateId = 'tpl-1';
        $details = new TemplatePatchDetails(
            template_description: 'Updated description',
            template_contract: new TemplateContractDetails(
                minimum_age: 21,
                pay_duration: new RelativeTime(5400),
                summary: 'Async update',
                currency: 'EUR',
                amount: 'EUR:30.00',
            )
        );

        $promise = new Promise();

        $this->response->method('getStatusCode')->willReturn(204);
        $this->stream->method('__toString')->willReturn('');
        $this->response->method('getBody')->willReturn($this->stream);

        $this->httpClientWrapper->expects($this->once())
            ->method('requestAsync')
            ->with('PATCH', "private/templates/{$templateId}", [], $this->anything())
            ->willReturn($promise);

        $result = UpdateTemplate::runAsync($this->client, $templateId, $details);
        $promise->resolve($this->response);

        $this->assertNull($result->wait());
    }
}




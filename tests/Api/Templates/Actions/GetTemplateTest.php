<?php

namespace Taler\Tests\Api\Templates\Actions;

use GuzzleHttp\Promise\Promise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Taler\Api\Templates\Actions\GetTemplate;
use Taler\Api\Templates\Dto\TemplateDetails;
use Taler\Api\Templates\Dto\TemplateContractDetails;
use Taler\Api\Templates\TemplatesClient;
use Taler\Api\Dto\RelativeTime;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

class GetTemplateTest extends TestCase
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
        $templateId = 'tpl-123';
        $payload = [
            'template_description' => 'Invoice template',
            'template_contract' => [
                'summary' => 'Service fee',
                'currency' => 'EUR',
                'amount' => 'EUR:10.00',
                'minimum_age' => 18,
                'pay_duration' => ['d_us' => 3600000000],
            ],
            'otp_id' => 'otp-1',
            'editable_defaults' => ['summary' => 'Editable'],
            'required_currency' => 'EUR',
        ];

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn(json_encode($payload));
        $this->response->method('getBody')->willReturn($this->stream);

        $headers = ['X-Test' => 'test'];

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('GET', "private/templates/{$templateId}", $headers)
            ->willReturn($this->response);

        $result = GetTemplate::run($this->client, $templateId, $headers);

        $this->assertInstanceOf(TemplateDetails::class, $result);
        $this->assertSame('Invoice template', $result->template_description);
        $this->assertInstanceOf(TemplateContractDetails::class, $result->template_contract);
        $this->assertSame('EUR:10.00', $result->template_contract->amount);
        $this->assertInstanceOf(RelativeTime::class, $result->template_contract->pay_duration);
        $this->assertSame('otp-1', $result->otp_id);
        $this->assertIsArray($result->editable_defaults);
        $this->assertSame('EUR', $result->required_currency);
    }

    public function testRunWithTalerException(): void
    {
        $this->expectException(TalerException::class);
        $this->httpClientWrapper->method('request')->willThrowException(new TalerException('boom'));
        GetTemplate::run($this->client, 'tpl-123');
    }

    public function testRunAsync(): void
    {
        $templateId = 'tpl-123';
        $payload = [
            'template_description' => 'Invoice template',
            'template_contract' => [
                'minimum_age' => 21,
                'pay_duration' => ['d_us' => 7200000000],
            ],
        ];

        $promise = new Promise();

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn(json_encode($payload));
        $this->response->method('getBody')->willReturn($this->stream);

        $this->httpClientWrapper->expects($this->once())
            ->method('requestAsync')
            ->with('GET', "private/templates/{$templateId}", [])
            ->willReturn($promise);

        $result = GetTemplate::runAsync($this->client, $templateId);
        $promise->resolve($this->response);

        $resolved = $result->wait();
        $this->assertInstanceOf(TemplateDetails::class, $resolved);
        $this->assertSame(21, $resolved->template_contract->minimum_age);
    }
}



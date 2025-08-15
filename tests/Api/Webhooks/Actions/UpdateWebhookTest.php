<?php

namespace Taler\Tests\Api\Webhooks\Actions;

use GuzzleHttp\Promise\Promise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Taler\Api\Webhooks\Actions\UpdateWebhook;
use Taler\Api\Webhooks\Dto\WebhookPatchDetails;
use Taler\Api\Webhooks\WebhooksClient;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

class UpdateWebhookTest extends TestCase
{
    private WebhooksClient $client;
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

        $this->client = new WebhooksClient($this->taler, $this->httpClientWrapper);
    }

    public function testRunSuccess(): void
    {
        $webhookId = 'wh-1';
        $details = new WebhookPatchDetails(
            event_type: 'order.paid',
            url: 'https://example.com/webhook',
            http_method: 'POST',
            header_template: 'X-Header: value',
            body_template: '{"id":"{{order_id}}"}'
        );

        $this->response->method('getStatusCode')->willReturn(204);
        $this->stream->method('__toString')->willReturn('');
        $this->response->method('getBody')->willReturn($this->stream);

        $headers = ['X-Test' => 'test'];

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('PATCH', "private/webhooks/{$webhookId}", $headers, $this->anything())
            ->willReturn($this->response);

        UpdateWebhook::run($this->client, $webhookId, $details, $headers);
        $this->addToAssertionCount(1);
    }

    public function testRunWithTalerException(): void
    {
        $this->expectException(TalerException::class);

        $webhookId = 'wh-1';
        $details = new WebhookPatchDetails('event', 'https://x', 'POST');

        $this->httpClientWrapper->method('request')->willThrowException(new TalerException('boom'));
        UpdateWebhook::run($this->client, $webhookId, $details);
    }

    public function testRunAsync(): void
    {
        $webhookId = 'wh-1';
        $details = new WebhookPatchDetails('event', 'https://x', 'POST');

        $promise = new Promise();

        $this->response->method('getStatusCode')->willReturn(204);
        $this->stream->method('__toString')->willReturn('');
        $this->response->method('getBody')->willReturn($this->stream);

        $this->httpClientWrapper->expects($this->once())
            ->method('requestAsync')
            ->with('PATCH', "private/webhooks/{$webhookId}", [], $this->anything())
            ->willReturn($promise);

        $result = UpdateWebhook::runAsync($this->client, $webhookId, $details);
        $promise->resolve($this->response);

        $this->assertNull($result->wait());
    }
}



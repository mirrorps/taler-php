<?php

namespace Taler\Tests\Api\Webhooks\Actions;

use GuzzleHttp\Promise\Promise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Taler\Api\Webhooks\Actions\GetWebhooks;
use Taler\Api\Webhooks\Dto\WebhookEntry;
use Taler\Api\Webhooks\Dto\WebhookSummaryResponse;
use Taler\Api\Webhooks\WebhooksClient;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

class GetWebhooksTest extends TestCase
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
        $expected = [
            'webhooks' => [
                ['webhook_id' => 'wh-1', 'event_type' => 'order.paid'],
                ['webhook_id' => 'wh-2', 'event_type' => 'order.refunded'],
            ]
        ];

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn(json_encode($expected));
        $this->response->method('getBody')->willReturn($this->stream);

        $headers = ['X-Test' => 'test'];

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('GET', 'private/webhooks', $headers)
            ->willReturn($this->response);

        $result = GetWebhooks::run($this->client, $headers);

        $this->assertInstanceOf(WebhookSummaryResponse::class, $result);
        $this->assertCount(2, $result->webhooks);
        $this->assertInstanceOf(WebhookEntry::class, $result->webhooks[0]);
        $this->assertSame('wh-1', $result->webhooks[0]->webhook_id);
    }

    public function testRunWithTalerException(): void
    {
        $this->expectException(TalerException::class);

        $this->httpClientWrapper->method('request')->willThrowException(new TalerException('boom'));
        GetWebhooks::run($this->client);
    }

    public function testRunAsync(): void
    {
        $expected = [
            'webhooks' => [
                ['webhook_id' => 'wh-1', 'event_type' => 'order.paid']
            ]
        ];

        $promise = new Promise();

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn(json_encode($expected));
        $this->response->method('getBody')->willReturn($this->stream);

        $this->httpClientWrapper->expects($this->once())
            ->method('requestAsync')
            ->with('GET', 'private/webhooks', [])
            ->willReturn($promise);

        $result = GetWebhooks::runAsync($this->client);
        $promise->resolve($this->response);

        $this->assertInstanceOf(WebhookSummaryResponse::class, $result->wait());
        $this->assertSame('wh-1', $result->wait()->webhooks[0]->webhook_id);
    }
}



<?php

namespace Taler\Tests\Api\Webhooks\Actions;

use GuzzleHttp\Promise\Promise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Taler\Api\Webhooks\Actions\GetWebhook;
use Taler\Api\Webhooks\Dto\WebhookDetails;
use Taler\Api\Webhooks\WebhooksClient;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

class GetWebhookTest extends TestCase
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
		$expected = [
			'event_type' => 'order.paid',
			'url' => 'https://example.com/webhook',
			'http_method' => 'POST',
			'header_template' => 'X-Header: value',
			'body_template' => '{"id":"{{order_id}}"}'
		];

		$this->response->method('getStatusCode')->willReturn(200);
		$this->stream->method('__toString')->willReturn(json_encode($expected));
		$this->response->method('getBody')->willReturn($this->stream);

		$headers = ['X-Test' => 'test'];

		$this->httpClientWrapper->expects($this->once())
			->method('request')
			->with('GET', "private/webhooks/{$webhookId}", $headers)
			->willReturn($this->response);

		$result = GetWebhook::run($this->client, $webhookId, $headers);

		$this->assertInstanceOf(WebhookDetails::class, $result);
		$this->assertSame('order.paid', $result->event_type);
		$this->assertSame('POST', $result->http_method);
	}

	public function testRunWithTalerException(): void
	{
		$this->expectException(TalerException::class);

		$this->httpClientWrapper->method('request')->willThrowException(new TalerException('boom'));
		GetWebhook::run($this->client, 'wh-1');
	}

	public function testRunAsync(): void
	{
		$webhookId = 'wh-1';
		$expected = [
			'event_type' => 'order.paid',
			'url' => 'https://example.com/webhook',
			'http_method' => 'POST'
		];

		$promise = new Promise();

		$this->response->method('getStatusCode')->willReturn(200);
		$this->stream->method('__toString')->willReturn(json_encode($expected));
		$this->response->method('getBody')->willReturn($this->stream);

		$this->httpClientWrapper->expects($this->once())
			->method('requestAsync')
			->with('GET', "private/webhooks/{$webhookId}", [])
			->willReturn($promise);

		$result = GetWebhook::runAsync($this->client, $webhookId);
		$promise->resolve($this->response);

		$this->assertInstanceOf(WebhookDetails::class, $result->wait());
		$this->assertSame('order.paid', $result->wait()->event_type);
	}
}

<?php

namespace Taler\Tests\Api\Webhooks\Actions;

use GuzzleHttp\Promise\Promise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Taler\Api\Webhooks\Actions\DeleteWebhook;
use Taler\Api\Webhooks\WebhooksClient;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

class DeleteWebhookTest extends TestCase
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

		$this->response->method('getStatusCode')->willReturn(204);
		$this->stream->method('__toString')->willReturn('');
		$this->response->method('getBody')->willReturn($this->stream);

		$headers = ['X-Test' => 'test'];

		$this->httpClientWrapper->expects($this->once())
			->method('request')
			->with('DELETE', "private/webhooks/{$webhookId}", $headers)
			->willReturn($this->response);

		DeleteWebhook::run($this->client, $webhookId, $headers);
		$this->addToAssertionCount(1);
	}

	public function testRunWithTalerException(): void
	{
		$this->expectException(TalerException::class);

		$this->httpClientWrapper->method('request')->willThrowException(new TalerException('boom'));
		DeleteWebhook::run($this->client, 'wh-1');
	}

	public function testRunAsync(): void
	{
		$webhookId = 'wh-1';

		$promise = new Promise();

		$this->response->method('getStatusCode')->willReturn(204);
		$this->stream->method('__toString')->willReturn('');
		$this->response->method('getBody')->willReturn($this->stream);

		$this->httpClientWrapper->expects($this->once())
			->method('requestAsync')
			->with('DELETE', "private/webhooks/{$webhookId}", [])
			->willReturn($promise);

		$result = DeleteWebhook::runAsync($this->client, $webhookId);
		$promise->resolve($this->response);

		$this->assertNull($result->wait());
	}
}

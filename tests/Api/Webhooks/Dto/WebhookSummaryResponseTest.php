<?php

namespace Taler\Tests\Api\Webhooks\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Webhooks\Dto\WebhookEntry;
use Taler\Api\Webhooks\Dto\WebhookSummaryResponse;

class WebhookSummaryResponseTest extends TestCase
{
	public function testCreateFromArray(): void
	{
		$data = [
			'webhooks' => [
				['webhook_id' => 'wh-1', 'event_type' => 'order.paid'],
				['webhook_id' => 'wh-2', 'event_type' => 'order.refunded'],
			]
		];

		$response = WebhookSummaryResponse::createFromArray($data);

		$this->assertCount(2, $response->webhooks);
		$this->assertInstanceOf(WebhookEntry::class, $response->webhooks[0]);
		$this->assertSame('wh-1', $response->webhooks[0]->webhook_id);
		$this->assertSame('order.paid', $response->webhooks[0]->event_type);
		$this->assertSame('wh-2', $response->webhooks[1]->webhook_id);
		$this->assertSame('order.refunded', $response->webhooks[1]->event_type);
	}
}

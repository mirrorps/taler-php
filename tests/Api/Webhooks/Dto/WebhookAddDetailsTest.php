<?php

namespace Taler\Tests\Api\Webhooks\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Webhooks\Dto\WebhookAddDetails;

class WebhookAddDetailsTest extends TestCase
{
    public function testCreateFromArrayAndValidation(): void
    {
        $data = [
            'webhook_id' => 'wh-1',
            'event_type' => 'order.paid',
            'url' => 'https://example.com/webhook',
            'http_method' => 'POST',
            'header_template' => 'X-Test: {{value}}',
            'body_template' => '{"id":"{{order_id}}"}'
        ];

        $details = WebhookAddDetails::createFromArray($data);

        $this->assertSame('wh-1', $details->webhook_id);
        $this->assertSame('order.paid', $details->event_type);
        $this->assertSame('https://example.com/webhook', $details->url);
        $this->assertSame('POST', $details->http_method);
        $this->assertSame('X-Test: {{value}}', $details->header_template);
        $this->assertSame('{"id":"{{order_id}}"}', $details->body_template);
    }

    public function testValidationFailsOnEmptyRequiredFields(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new WebhookAddDetails('', 'event', 'https://x', 'POST');
    }

    public function testValidationFailsOnInvalidMethod(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new WebhookAddDetails('id', 'event', 'https://x', 'INVALID');
    }

    public function testOptionalTemplatesCanBeNull(): void
    {
        $details = new WebhookAddDetails('id', 'event', 'https://x', 'GET');
        $this->assertNull($details->header_template);
        $this->assertNull($details->body_template);
    }
}



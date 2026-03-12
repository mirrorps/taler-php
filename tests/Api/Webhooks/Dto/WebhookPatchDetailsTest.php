<?php

namespace Taler\Tests\Api\Webhooks\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Webhooks\Dto\WebhookPatchDetails;
use Taler\Api\Dto\Url;

class WebhookPatchDetailsTest extends TestCase
{
    public function testCreateFromArrayAndValidation(): void
    {
        $data = [
            'event_type' => 'order.paid',
            'url' => Url::fromString('https://example.com/webhook'),
            'http_method' => 'POST',
            'header_template' => 'X-Test: {{value}}',
            'body_template' => '{"id":"{{order_id}}"}',
        ];

        $details = WebhookPatchDetails::createFromArray($data);

        $this->assertSame('order.paid', $details->event_type);
        $this->assertInstanceOf(Url::class, $details->url);
        $this->assertSame('https://example.com/webhook', (string) $details->url);
        $this->assertSame('POST', $details->http_method);
        $this->assertSame('X-Test: {{value}}', $details->header_template);
        $this->assertSame('{"id":"{{order_id}}"}', $details->body_template);
    }

    public function testValidationFailsOnEmptyEventType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new WebhookPatchDetails('', Url::fromString('https://x'), 'POST');
    }

    public function testValidationFailsOnInvalidMethod(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new WebhookPatchDetails('event', Url::fromString('https://x'), 'INVALID');
    }

    public function testOptionalTemplatesCanBeNull(): void
    {
        $details = new WebhookPatchDetails('event', Url::fromString('https://x'), 'GET');
        $this->assertNull($details->header_template);
        $this->assertNull($details->body_template);
    }

    public function testJsonEncodeSerializesUrlAsString(): void
    {
        $details = new WebhookPatchDetails('event', Url::fromString('https://example.com/hook'), 'POST');

        $this->assertSame(
            '{"event_type":"event","url":"https:\/\/example.com\/hook","http_method":"POST","header_template":null,"body_template":null}',
            json_encode($details, JSON_THROW_ON_ERROR)
        );
    }
}



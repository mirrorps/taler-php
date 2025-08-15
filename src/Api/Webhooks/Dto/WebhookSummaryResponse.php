<?php

namespace Taler\Api\Webhooks\Dto;

/**
 * DTO for the list of webhooks response.
 *
 * Response shape per docs:
 * { "webhooks": [ { webhook_id, event_type }, ... ] }
 *
 * Do not include data validation.
 */
class WebhookSummaryResponse
{
    /**
     * @param array<WebhookEntry> $webhooks
     */
    public function __construct(
        public readonly array $webhooks,
    ) {
    }

    /**
     * @param array{webhooks: array<int, array{webhook_id: string, event_type: string}>} $data
     */
    public static function createFromArray(array $data): self
    {
        $entries = array_map(
            static fn(array $d) => WebhookEntry::createFromArray($d),
            $data['webhooks']
        );

        return new self($entries);
    }
}



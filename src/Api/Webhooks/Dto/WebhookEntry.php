<?php

namespace Taler\Api\Webhooks\Dto;

/**
 * Describes a single webhook entry.
 *
 * Docs shape:
 * {
 *   webhook_id: string,
 *   event_type: string
 * }
 *
 * No validation for response DTOs.
 */
class WebhookEntry
{
    public function __construct(
        public readonly string $webhook_id,
        public readonly string $event_type,
    ) {
    }

    /**
     * @param array{webhook_id: string, event_type: string} $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            webhook_id: $data['webhook_id'],
            event_type: $data['event_type']
        );
    }
}



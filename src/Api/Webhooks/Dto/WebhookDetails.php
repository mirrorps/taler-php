<?php

namespace Taler\Api\Webhooks\Dto;

/**
 * Webhook details response DTO.
 *
 * Docs shape:
 * {
 *   event_type: string,
 *   url: string,
 *   http_method: string,
 *   header_template?: string,
 *   body_template?: string
 * }
 *
 * No validation for response DTOs.
 */
class WebhookDetails
{
    public function __construct(
        public readonly string $event_type,
        public readonly string $url,
        public readonly string $http_method,
        public readonly ?string $header_template = null,
        public readonly ?string $body_template = null,
    ) {
    }

    /**
     * @param array{
     *   event_type: string,
     *   url: string,
     *   http_method: string,
     *   header_template?: string|null,
     *   body_template?: string|null
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            event_type: $data['event_type'],
            url: $data['url'],
            http_method: $data['http_method'],
            header_template: $data['header_template'] ?? null,
            body_template: $data['body_template'] ?? null,
        );
    }
}



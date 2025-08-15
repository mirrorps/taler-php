<?php

namespace Taler\Api\Webhooks\Dto;

/**
 * DTO for updating a Webhook.
 *
 * Docs: PATCH [/instances/$INSTANCES]/private/webhooks/$WEBHOOK_ID
 */
class WebhookPatchDetails
{
    /**
     * @param string $event_type The event of the webhook: why the webhook is used
     * @param string $url URL of the webhook where the customer will be redirected
     * @param string $http_method Method used by the webhook
     * @param string|null $header_template Header template of the webhook
     * @param string|null $body_template Body template by the webhook
     * @param bool $validate Whether to validate inputs
     */
    public function __construct(
        public readonly string $event_type,
        public readonly string $url,
        public readonly string $http_method,
        public readonly ?string $header_template = null,
        public readonly ?string $body_template = null,
        bool $validate = true
    ) {
        if ($validate) {
            $this->validate();
        }
    }

    /**
     * @param array{
     *   event_type: string,
     *   url: string,
     *   http_method: string,
     *   header_template?: string,
     *   body_template?: string
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            event_type: $data['event_type'],
            url: $data['url'],
            http_method: $data['http_method'],
            header_template: $data['header_template'] ?? null,
            body_template: $data['body_template'] ?? null
        );
    }

    public function validate(): void
    {
        if ($this->event_type === '' || trim($this->event_type) === '') {
            throw new \InvalidArgumentException('event_type must not be empty');
        }

        if ($this->url === '' || trim($this->url) === '') {
            throw new \InvalidArgumentException('url must not be empty');
        }

        $allowedMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'];
        $method = strtoupper($this->http_method);
        if (!in_array($method, $allowedMethods, true)) {
            throw new \InvalidArgumentException('http_method must be one of: ' . implode(', ', $allowedMethods));
        }

        if ($this->header_template !== null && trim($this->header_template) === '') {
            throw new \InvalidArgumentException('header_template, when provided, must not be empty');
        }

        if ($this->body_template !== null && trim($this->body_template) === '') {
            throw new \InvalidArgumentException('body_template, when provided, must not be empty');
        }
    }
}



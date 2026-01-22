<?php

namespace Taler\Api\Order\Dto;

/**
 * Request DTO for querying order history (GET /private/orders)
 *
 * Docs: https://docs.taler.net/core/api-merchant.html#inspecting-orders
 *
 * Boolean-like filters are encoded as "yes"/"no" per spec.
 * Omitted (null) means "all" / no filtering.
 */
class GetOrdersRequest
{
    /**
     * @param array<string, scalar|null> $extraParams Extra (forward-compatible) query parameters
     */
    public function __construct(
        public readonly ?bool $paid = null,
        public readonly ?bool $refunded = null,
        public readonly ?bool $wired = null,
        public readonly ?int $limit = null,
        public readonly ?int $date_s = null,
        public readonly ?int $offset = null,
        public readonly ?int $timeout_ms = null,
        public readonly ?string $session_id = null,
        public readonly ?string $fulfillment_url = null,
        public readonly ?string $summary_filter = null,
        public readonly array $extraParams = [],
    ) {}

    /**
     * @return array<string, scalar>
     */
    public function toArray(): array
    {
        $params = [];

        if ($this->paid !== null) {
            $params['paid'] = $this->paid ? 'yes' : 'no';
        }
        if ($this->refunded !== null) {
            $params['refunded'] = $this->refunded ? 'yes' : 'no';
        }
        if ($this->wired !== null) {
            $params['wired'] = $this->wired ? 'yes' : 'no';
        }
        if ($this->limit !== null) {
            $params['limit'] = $this->limit;
        }
        if ($this->date_s !== null) {
            $params['date_s'] = $this->date_s;
        }
        if ($this->offset !== null) {
            $params['offset'] = $this->offset;
        }
        if ($this->timeout_ms !== null) {
            $params['timeout_ms'] = $this->timeout_ms;
        }
        if ($this->session_id !== null) {
            $params['session_id'] = $this->session_id;
        }
        if ($this->fulfillment_url !== null) {
            $params['fulfillment_url'] = $this->fulfillment_url;
        }
        if ($this->summary_filter !== null) {
            $params['summary_filter'] = $this->summary_filter;
        }

        foreach ($this->extraParams as $key => $value) {
            if ($value === null) {
                continue;
            }
            // Normalize booleans to match merchant API conventions.
            if (is_bool($value)) {
                $params[$key] = $value ? 'yes' : 'no';
                continue;
            }
            $params[$key] = $value;
        }

        return $params;
    }
}


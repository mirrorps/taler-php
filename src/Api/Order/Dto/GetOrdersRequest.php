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
     * Query Parameters (GNU Taler docs: https://docs.taler.net/core/api-merchant.html#inspecting-orders)
     *
     * @param ?bool $paid Optional. If set to yes, only return paid orders, if no only unpaid orders. Do not give (or use “all”) to see all orders regardless of payment status.
     * @param ?bool $refunded Optional. If set to yes, only return refunded orders, if no only unrefunded orders. Do not give (or use “all”) to see all orders regardless of refund status.
     * @param ?bool $wired Optional. If set to yes, only return wired orders, if no only orders with missing wire transfers. Do not give (or use “all”) to see all orders regardless of wire transfer status.
     * @param ?int $limit Optional. At most return the given number of results. Negative for descending by row ID, positive for ascending by row ID. Default is 20. Since protocol v12.
     * @param ?int $date_s Optional. Non-negative date in seconds after the UNIX Epoc, if not specified, we default to the oldest or most recent entry, depending on limit.
     * @param ?int $offset Optional. Starting row_id for an iteration. Since protocol v12.
     * @param ?int $timeout_ms Optional. Timeout in milliseconds to wait for additional orders if the answer would otherwise be negative (long polling). Only useful if limit is positive. Note that the merchant MAY still return a response that contains fewer than limit orders.
     * @param ?string $session_id Optional. Since protocol v6. Filters by session ID.
     * @param ?string $fulfillment_url Optional. Since protocol v6. Filters by fulfillment URL.
     * @param ?string $summary_filter Optional. Only returns orders where the summary contains the given text as a substring. Matching is case-insensitive. Since protocol v23.
     * @param array<string, scalar|null> $extraParams Extra (forward-compatible) query parameters for power users.
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


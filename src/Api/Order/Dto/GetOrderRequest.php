<?php

namespace Taler\Api\Order\Dto;

/**
 * Request DTO for querying a single order (GET /private/orders/$ORDER_ID)
 *
 * Docs: https://docs.taler.net/core/api-merchant.html#querying-payment-status
 *
 * Notes:
 * - Boolean-like parameters are encoded as "yes"/"no" (or "YES"/"NO" where explicitly stated in docs).
 * - Omitted (null) means the query parameter is not sent.
 */
class GetOrderRequest
{
    /**
     * Query Parameters (GNU Taler docs)
     *
     * @param ?string $h_contract Optional. Hash of the order’s contract terms (this is used to authenticate the wallet/customer in case $ORDER_ID is guessable). Required once an order was claimed.
     * @param ?string $token Optional. Authorizes the request via the claim token that was returned in the PostOrderResponse. Used with unclaimed orders only. Whether token authorization is required is determined by the merchant when the frontend creates the order.
     * @param ?string $session_id Optional. Session ID that the payment must be bound to. If not specified, the payment is not session-bound.
     * @param ?int $timeout_ms Optional. If specified, the merchant backend will wait up to timeout_ms milliseconds for completion of the payment before sending the HTTP response. A client must never rely on this behavior, as the merchant backend may return a response immediately.
     * @param ?bool $await_refund_obtained Optional. If set to “yes”, poll for the order’s pending refunds to be picked up. timeout_ms specifies how long we will wait for the refund.
     * @param ?string $refund Optional. Indicates that we are polling for a refund above the given AMOUNT. timeout_ms will specify how long we will wait for the refund.
     * @param ?bool $allow_refunded_for_repurchase Optional. Since protocol v9 refunded orders are only returned under “already_paid_order_id” if this flag is set explicitly to “YES”.
     * @param array<string, scalar|null> $extraParams Extra (forward-compatible) query parameters for power users.
     */
    public function __construct(
        public readonly ?string $h_contract = null,
        public readonly ?string $token = null,
        public readonly ?string $session_id = null,
        public readonly ?int $timeout_ms = null,
        public readonly ?bool $await_refund_obtained = null,
        public readonly ?string $refund = null, //--- $refund type AMOUNT ("EUR:1.50") translates to string
        public readonly ?bool $allow_refunded_for_repurchase = null,
        public readonly array $extraParams = [],
    ) {}

    /**
     * @return array<string, scalar>
     */
    public function toArray(): array
    {
        $params = [];

        if ($this->h_contract !== null) {
            $params['h_contract'] = $this->h_contract;
        }
        if ($this->token !== null) {
            $params['token'] = $this->token;
        }
        if ($this->session_id !== null) {
            $params['session_id'] = $this->session_id;
        }
        if ($this->timeout_ms !== null) {
            $params['timeout_ms'] = $this->timeout_ms;
        }
        if ($this->await_refund_obtained !== null) {
            $params['await_refund_obtained'] = $this->await_refund_obtained ? 'yes' : 'no';
        }
        if ($this->refund !== null) {
            $params['refund'] = $this->refund;
        }
        if ($this->allow_refunded_for_repurchase !== null) {
            // Docs explicitly mention "YES"; keep that casing for true.
            $params['allow_refunded_for_repurchase'] = $this->allow_refunded_for_repurchase ? 'YES' : 'NO';
        }

        foreach ($this->extraParams as $key => $value) {
            if ($value === null) {
                continue;
            }
            if (is_bool($value)) {
                $params[$key] = $value ? 'yes' : 'no';
                continue;
            }
            $params[$key] = $value;
        }

        return $params;
    }
}


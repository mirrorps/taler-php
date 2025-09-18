<?php

namespace Taler\Api\Instance\Dto;

/**
 * Request DTO for checking KYC status of a particular payment target
 *
 * Endpoint: GET /instances/$INSTANCE/private/kyc
 *
 * Query parameters:
 * - h_wire: Optional. If specified, filter by this salted payto hash
 * - exchange_url: Optional. If specified, filter by this exchange URL
 * - lpt: Optional. Long-poll target. Use 1 (auth token), 2 (AML done), 3 (KYC OK)
 * - timeout_ms: Optional. Max time in milliseconds to wait for completion
 */
class GetKycStatusRequest
{
    /**
     * @param string|null $h_wire Filter for a specific wire account (salted payto hash)
     * @param string|null $exchange_url Filter for a specific exchange base URL
     * @param int|null $lpt Long-poll target, one of 1, 2, 3
     * @param int|null $timeout_ms Wait time in milliseconds, must be > 0 if provided
     * @param bool $validate Whether to validate the data automatically
     */
    public function __construct(
        public readonly ?string $h_wire = null,
        public readonly ?string $exchange_url = null,
        public readonly ?int $lpt = null,
        public readonly ?int $timeout_ms = null,
        bool $validate = true
    ) {
        if ($validate) {
            $this->validate();
        }
    }

    /**
     * Validate inputs according to docs
     */
    public function validate(): void
    {
        if ($this->lpt !== null) {
            $allowed = [1, 2, 3];
            if (!in_array($this->lpt, $allowed, true)) {
                throw new \InvalidArgumentException('lpt must be one of 1, 2 or 3');
            }
        }

        if ($this->timeout_ms !== null) {
            if ($this->timeout_ms <= 0) {
                throw new \InvalidArgumentException('timeout_ms must be a positive integer');
            }
        }
    }

    /**
     * @return array<string, string|int>
     */
    public function toArray(): array
    {
        $params = [];
        if ($this->h_wire !== null) {
            $params['h_wire'] = $this->h_wire;
        }
        if ($this->exchange_url !== null) {
            $params['exchange_url'] = $this->exchange_url;
        }
        if ($this->lpt !== null) {
            $params['lpt'] = $this->lpt;
        }
        if ($this->timeout_ms !== null) {
            $params['timeout_ms'] = $this->timeout_ms;
        }
        return $params;
    }
}




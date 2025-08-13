<?php

namespace Taler\Api\WireTransfers\Dto;

/**
 * Request DTO for querying merchant wire transfers
 *
 * @see https://docs.taler.net/core/api-merchant.html#get-[-instances-$INSTANCE]-private-transfers
 */
class GetTransfersRequest
{
    public function __construct(
        public readonly ?string $payto_uri = null,
        public readonly ?string $before = null,
        public readonly ?string $after = null,
        public readonly ?int $limit = null,
        public readonly ?int $offset = null,
        public readonly ?string $expected = null,
    ) {}

    /**
     * @return array<string, string|int>
     */
    public function toArray(): array
    {
        $params = [];

        if ($this->payto_uri !== null) {
            $params['payto_uri'] = $this->payto_uri;
        }
        if ($this->before !== null) {
            $params['before'] = $this->before;
        }
        if ($this->after !== null) {
            $params['after'] = $this->after;
        }
        if ($this->limit !== null) {
            $params['limit'] = $this->limit;
        }
        if ($this->offset !== null) {
            $params['offset'] = $this->offset;
        }
        if ($this->expected !== null) {
            $params['expected'] = $this->expected;
        }

        return $params;
    }
}



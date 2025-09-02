<?php

namespace Taler\Api\Instance\Dto;

/**
 * Request DTO for querying access tokens list
 *
 * @since v19
 */
class GetAccessTokensRequest
{
    /**
     * @param int|null $limit At most return the given number of results. Negative for descending by serial, positive for ascending by serial. Defaults to -20.
     * @param int|null $offset Starting serial for pagination
     * @param bool $validate Whether to validate the data automatically
     */
    public function __construct(
        public readonly ?int $limit = null,
        public readonly ?int $offset = null,
        bool $validate = true
    ) {
        if ($validate) {
            $this->validate();
        }
    }

    /**
     * Validate inputs
     */
    public function validate(): void
    {
        if ($this->limit !== null && $this->limit === 0) {
            throw new \InvalidArgumentException('limit cannot be zero');
        }
        if ($this->offset !== null && $this->offset < 0) {
            throw new \InvalidArgumentException('offset must be non-negative');
        }
    }

    /**
     * @return array<string, string|int>
     */
    public function toArray(): array
    {
        $params = [];
        if ($this->limit !== null) {
            $params['limit'] = $this->limit;
        }
        if ($this->offset !== null) {
            $params['offset'] = $this->offset;
        }
        return $params;
    }
}



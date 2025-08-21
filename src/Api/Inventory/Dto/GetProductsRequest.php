<?php

namespace Taler\Api\Inventory\Dto;

/**
 * Request DTO for querying inventory products list
 *
 * Query parameters:
 * - limit: Optional. At most return the given number of results. Negative for descending by row ID,
 *          positive for ascending by row ID. Default is 20.
 * - offset: Optional. Starting product_serial_id for an iteration.
 */
class GetProductsRequest
{
    public function __construct(
        public readonly ?int $limit = null,
        public readonly ?int $offset = null,
    ) {}

    /**
     * @return array<string, int>
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



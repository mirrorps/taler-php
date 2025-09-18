<?php

namespace Taler\Api\Instance\Dto;

/**
 * Request DTO for merchant statistics counter endpoint
 *
 * Query parameters:
 * - by: Optional. If set to "BUCKET", only statistics by bucket will be returned.
 *       If set to "INTERVAL", only statistics kept by interval will be returned.
 *       If not set or set to "ANY", both will be returned.
 */
class GetMerchantStatisticsCounterRequest
{
    /**
     * @param "BUCKET"|"INTERVAL"|"ANY"|null $by Filter which statistics to return
     * @param bool $validate Whether to validate the data automatically
     */
    public function __construct(
        public readonly ?string $by = null,
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
        if ($this->by !== null) {
            $allowed = [
                'BUCKET',
                'INTERVAL',
                'ANY'
            ];
            if (!in_array($this->by, $allowed, true)) {
                throw new \InvalidArgumentException('by must be one of BUCKET, INTERVAL or ANY');
            }
        }
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        $params = [];
        if ($this->by !== null) {
            $params['by'] = $this->by;
        }
        return $params;
    }
}




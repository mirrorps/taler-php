<?php

namespace Taler\Api\Dto;

/**
 * DTO for absolute timestamp
 * 
 * @see https://docs.taler.net/core/api-common.html
 */
class Timestamp
{
    /**
     * @param int|string $t_s Timestamp in seconds since Unix epoch or "never" to represent the end of time
     */
    public function __construct(
        public readonly int|string $t_s,
    ) {
        if (is_int($t_s) && $t_s < 0) {
            throw new \InvalidArgumentException('Timestamp must be non-negative');
        }
        if (is_string($t_s) && $t_s !== 'never') {
            throw new \InvalidArgumentException('String timestamp can only be "never"');
        }
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param array{t_s: int|string} $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            t_s: $data['t_s']
        );
    }
} 
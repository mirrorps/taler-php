<?php

namespace Taler\Api\Dto;

/**
 * DTO for relative time duration
 * 
 * @see https://docs.taler.net/core/api-common.html
 */
class RelativeTime
{
    private const MAX_MICROSECONDS = 9007199254740991; // 2^53 - 1

    /**
     * @param int|string $d_us Duration in microseconds or "forever" to represent an infinite duration
     */
    public function __construct(
        public readonly int|string $d_us,
    ) {
        if (is_int($d_us) && ($d_us < 0 || $d_us > self::MAX_MICROSECONDS)) {
            throw new \InvalidArgumentException(
                'Duration must be between 0 and ' . self::MAX_MICROSECONDS . ' microseconds or "forever"'
            );
        }
        if (is_string($d_us) && $d_us !== 'forever') {
            throw new \InvalidArgumentException('String duration can only be "forever"');
        }
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param array{d_us: int|string} $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            d_us: $data['d_us']
        );
    }
} 
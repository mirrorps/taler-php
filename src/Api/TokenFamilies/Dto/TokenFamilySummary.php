<?php

namespace Taler\Api\TokenFamilies\Dto;

use Taler\Api\Dto\Timestamp;

/**
 * Token family summary DTO (response only, no validation).
 */
class TokenFamilySummary
{
    public function __construct(
        public readonly string $slug,
        public readonly string $name,
        public readonly Timestamp $valid_after,
        public readonly Timestamp $valid_before,
        public readonly string $kind,
    ) {
    }

    /**
     * @param array{
     *   slug: string,
     *   name: string,
     *   valid_after: array{t_s: int|string},
     *   valid_before: array{t_s: int|string},
     *   kind: string
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            slug: $data['slug'],
            name: $data['name'],
            valid_after: Timestamp::fromArray($data['valid_after']),
            valid_before: Timestamp::fromArray($data['valid_before']),
            kind: $data['kind'],
        );
    }
}



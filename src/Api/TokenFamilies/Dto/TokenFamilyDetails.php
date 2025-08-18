<?php

namespace Taler\Api\TokenFamilies\Dto;

use Taler\Api\Dto\RelativeTime;
use Taler\Api\Dto\Timestamp;

/**
 * Token family details response DTO.
 *
 * No validation for response DTOs.
 */
class TokenFamilyDetails
{
    public function __construct(
        public readonly string $slug,
        public readonly string $name,
        public readonly string $description,
        public readonly Timestamp $valid_after,
        public readonly Timestamp $valid_before,
        public readonly RelativeTime $duration,
        public readonly RelativeTime $validity_granularity,
        public readonly RelativeTime $start_offset,
        public readonly string $kind,
        public readonly int $issued,
        public readonly int $used,
        /** @var array<string,string>|null */
        public readonly ?array $description_i18n = null,
        /** @var array<string,mixed>|null */
        public readonly ?array $extra_data = null,
    ) {
    }

    /**
     * @param array{
     *   slug: string,
     *   name: string,
     *   description: string,
     *   description_i18n?: array<string,string>|null,
     *   extra_data?: array<string,mixed>|null,
     *   valid_after: array{t_s: int|string},
     *   valid_before: array{t_s: int|string},
     *   duration: array{d_us: int|string},
     *   validity_granularity: array{d_us: int|string},
     *   start_offset: array{d_us: int|string},
     *   kind: string,
     *   issued: int,
     *   used: int
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            slug: $data['slug'],
            name: $data['name'],
            description: $data['description'],
            valid_after: Timestamp::fromArray($data['valid_after']),
            valid_before: Timestamp::fromArray($data['valid_before']),
            duration: RelativeTime::fromArray($data['duration']),
            validity_granularity: RelativeTime::fromArray($data['validity_granularity']),
            start_offset: RelativeTime::fromArray($data['start_offset']),
            kind: $data['kind'],
            issued: (int) $data['issued'],
            used: (int) $data['used'],
            description_i18n: $data['description_i18n'] ?? null,
            extra_data: $data['extra_data'] ?? null,
        );
    }
}



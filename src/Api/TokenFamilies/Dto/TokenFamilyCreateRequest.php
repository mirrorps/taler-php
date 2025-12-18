<?php

namespace Taler\Api\TokenFamilies\Dto;

use Taler\Api\Dto\RelativeTime;
use Taler\Api\Dto\Timestamp;

/**
 * DTO for creating a Token Family.
 *
 * Docs: POST [/instances/$INSTANCES]/private/tokenfamilies
 */
class TokenFamilyCreateRequest implements \JsonSerializable
{
    /**
     * @param string $slug Identifier for the token family consisting of unreserved characters according to RFC 3986
     * @param string $name Human-readable name for the token family
     * @param string $description Human-readable description for the token family
     * @param array<string,string>|null $description_i18n Optional map from IETF BCP 47 language tags to localized descriptions
     * @param array<string,mixed>|null $extra_data Additional meta data, such as trusted_domains or expected_domains
     * @param Timestamp|null $valid_after Start time of the token family's validity period
     * @param Timestamp $valid_before End time of the token family's validity period
     * @param RelativeTime $duration Validity duration of an issued token
     * @param RelativeTime $validity_granularity Rounding granularity for the start validity of keys
     * @param RelativeTime $start_offset Offset to subtract from the start time rounded to validity_granularity
     * @param string $kind Kind of the token family (allowed: "discount", "subscription")
     * @param bool $validate Whether to validate the data upon construction
     */
    public function __construct(
        public readonly string $slug,
        public readonly string $name,
        public readonly string $description,
        public readonly Timestamp $valid_before,
        public readonly RelativeTime $duration,
        public readonly RelativeTime $validity_granularity,
        public readonly RelativeTime $start_offset,
        public readonly string $kind,
        public readonly ?array $description_i18n = null,
        public readonly ?array $extra_data = null,
        public readonly ?Timestamp $valid_after = null,
        bool $validate = true
    ) {
        if ($validate) {
            $this->validate();
        }
    }

    /**
     * @param array{
     *   slug: string,
     *   name: string,
     *   description: string,
     *   description_i18n?: array<string,string>,
     *   extra_data?: array<string,mixed>,
     *   valid_after?: array{t_s: int|string},
     *   valid_before: array{t_s: int|string},
     *   duration: array{d_us: int|string},
     *   validity_granularity: array{d_us: int|string},
     *   start_offset: array{d_us: int|string},
     *   kind: string
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            slug: $data['slug'],
            name: $data['name'],
            description: $data['description'],
            description_i18n: $data['description_i18n'] ?? null,
            extra_data: $data['extra_data'] ?? null,
            valid_after: isset($data['valid_after']) ? Timestamp::createFromArray($data['valid_after']) : null,
            valid_before: Timestamp::createFromArray($data['valid_before']),
            duration: RelativeTime::createFromArray($data['duration']),
            validity_granularity: RelativeTime::createFromArray($data['validity_granularity']),
            start_offset: RelativeTime::createFromArray($data['start_offset']),
            kind: $data['kind']
        );
    }

    /**
     * Validates the DTO data.
     *
     * @throws \InvalidArgumentException If validation fails
     */
    public function validate(): void
    {
        if ($this->slug === '' || trim($this->slug) === '') {
            throw new \InvalidArgumentException('slug must not be empty');
        }

        // RFC 3986 unreserved: ALPHA / DIGIT / "-" / "." / "_" / "~"
        if (!preg_match('/^[A-Za-z0-9\-._~]+$/', $this->slug)) {
            throw new \InvalidArgumentException('slug contains invalid characters; only unreserved RFC 3986 characters are allowed');
        }

        if ($this->name === '' || trim($this->name) === '') {
            throw new \InvalidArgumentException('name must not be empty');
        }

        if ($this->description === '' || trim($this->description) === '') {
            throw new \InvalidArgumentException('description must not be empty');
        }

        if ($this->kind !== 'discount' && $this->kind !== 'subscription') {
            throw new \InvalidArgumentException('kind must be either "discount" or "subscription"');
        }
    }

    /**
     * @return array{
     *   slug: string,
     *   name: string,
     *   description: string,
     *   description_i18n: array<string,string>|null,
     *   extra_data: array<string,mixed>|null,
     *   valid_after: Timestamp|null,
     *   valid_before: Timestamp,
     *   duration: RelativeTime,
     *   validity_granularity: RelativeTime,
     *   start_offset: RelativeTime,
     *   kind: string
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'description_i18n' => $this->description_i18n,
            'extra_data' => $this->extra_data,
            'valid_after' => $this->valid_after,
            'valid_before' => $this->valid_before,
            'duration' => $this->duration,
            'validity_granularity' => $this->validity_granularity,
            'start_offset' => $this->start_offset,
            'kind' => $this->kind,
        ];
    }
}



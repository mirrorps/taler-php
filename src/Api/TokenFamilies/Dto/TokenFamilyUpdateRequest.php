<?php

namespace Taler\Api\TokenFamilies\Dto;

use Taler\Api\Dto\Timestamp;

/**
 * DTO for updating a Token Family.
 *
 * Docs: PATCH [/instances/$INSTANCES]/private/tokenfamilies/$TOKEN_FAMILY_SLUG
 */
class TokenFamilyUpdateRequest implements \JsonSerializable
{
    /**
     * @param string $name Human-readable name for the token family
     * @param string $description Human-readable description for the token family
     * @param array<string,string>|null $description_i18n Optional map from IETF BCP 47 language tags to localized descriptions
     * @param array<string,mixed>|null $extra_data Additional meta data such as trusted/expected domains (depends on kind)
     * @param Timestamp $valid_after Start time of the token family's validity period
     * @param Timestamp $valid_before End time of the token family's validity period
     * @param bool $validate Whether to validate the data upon construction
     */
    public function __construct(
        public readonly string $name,
        public readonly string $description,
        public readonly Timestamp $valid_after,
        public readonly Timestamp $valid_before,
        public readonly ?array $description_i18n = null,
        public readonly ?array $extra_data = null,
        bool $validate = true
    ) {
        if ($validate) {
            $this->validate();
        }
    }

    /**
     * @param array{
     *   name: string,
     *   description: string,
     *   description_i18n?: array<string,string>,
     *   extra_data?: array<string,mixed>,
     *   valid_after: array{t_s: int|string},
     *   valid_before: array{t_s: int|string}
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            description: $data['description'],
            description_i18n: $data['description_i18n'] ?? null,
            extra_data: $data['extra_data'] ?? null,
            valid_after: Timestamp::fromArray($data['valid_after']),
            valid_before: Timestamp::fromArray($data['valid_before'])
        );
    }

    /**
     * Validates the DTO data.
     *
     * @throws \InvalidArgumentException If validation fails
     */
    public function validate(): void
    {
        if ($this->name === '' || trim($this->name) === '') {
            throw new \InvalidArgumentException('name must not be empty');
        }

        if ($this->description === '' || trim($this->description) === '') {
            throw new \InvalidArgumentException('description must not be empty');
        }
    }

    /**
     * @return array{
     *   name: string,
     *   description: string,
     *   description_i18n: array<string,string>|null,
     *   extra_data: array<string,mixed>|null,
     *   valid_after: Timestamp,
     *   valid_before: Timestamp
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'description_i18n' => $this->description_i18n,
            'extra_data' => $this->extra_data,
            'valid_after' => $this->valid_after,
            'valid_before' => $this->valid_before,
        ];
    }
}



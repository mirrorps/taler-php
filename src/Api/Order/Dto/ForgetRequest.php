<?php

namespace Taler\Api\Order\Dto;

/**
 * DTO for forget request data.
 *
 * @phpstan-type JsonPath string
 */
class ForgetRequest
{
    /**
     * @param array<JsonPath> $fields Array of valid JSON paths to forgettable fields in the order's contract terms
     * @param bool $validate Whether to validate the data automatically
     */
    public function __construct(
        public readonly array $fields,
        bool $validate = true
    ) {
        if ($validate) {
            $this->validate();
        }
    }

    /**
     * Creates a new instance from an array.
     *
     * @param array{fields: array<JsonPath>} $data The data array
     * @return self
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            fields: $data['fields']
        );
    }

    /**
     * Validates the DTO data.
     *
     * @throws \InvalidArgumentException If validation fails
     */
    public function validate(): void
    {
        if (empty($this->fields)) {
            throw new \InvalidArgumentException('Fields array cannot be empty');
        }

        foreach ($this->fields as $field) {

            if (!str_starts_with($field, '$.')) {
                throw new \InvalidArgumentException('Field must start with $.');
            }
            if (str_ends_with($field, ']') || str_ends_with($field, '[*]')) {
                throw new \InvalidArgumentException('Field cannot end with an array index or wildcard');
            }
        }
    }
}
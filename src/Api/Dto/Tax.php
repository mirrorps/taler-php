<?php

namespace Taler\Api\Dto;

/**
 * DTO for Tax data
 */
class Tax
{
    /**
     * @param string $name The name of the tax
     * @param string $tax Amount paid in tax
     */
    public function __construct(
        public readonly string $name,
        public readonly string $tax,
    ) {
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param array{
     *     name?: string,
     *     tax?: string
     * } $data
     * @return self
     * @throws \InvalidArgumentException When required data is missing or invalid
     */
    public static function fromArray(array $data): self
    {
        if (!isset($data['name'])) {
            throw new \InvalidArgumentException('Missing required field: name');
        }

        if (!isset($data['tax'])) {
            throw new \InvalidArgumentException('Missing required field: tax');
        }

        return new self(
            name: $data['name'],
            tax: $data['tax']
        );
    }
} 
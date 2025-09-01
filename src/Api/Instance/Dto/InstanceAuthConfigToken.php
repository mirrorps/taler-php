<?php

namespace Taler\Api\Instance\Dto;

/**
 * DTO for InstanceAuthConfigToken
 *
 * @since v19
 */
class InstanceAuthConfigToken
{
    const METHOD = 'token';

    /**
     * @param string $password Authentication password for basic auth
     * @param bool $validate Whether to validate the data automatically
     */
    public function __construct(
        public readonly string $password,
        bool $validate = true
    ) {
        if ($validate) {
            $this->validate();
        }
    }

    /**
     * Validates the DTO data.
     *
     * @throws \InvalidArgumentException If validation fails
     */
    public function validate(): void
    {
        if (empty($this->password)) {
            throw new \InvalidArgumentException('Password cannot be empty');
        }
    }

    /**
     * Creates a new instance from an array of data.
     *
     * @param array{
     *     method: "token",
     *     password: string
     * } $data
     * @return self
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            password: $data['password']
        );
    }
}

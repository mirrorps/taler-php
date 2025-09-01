<?php

namespace Taler\Api\Instance\Dto;

/**
 * DTO for InstanceAuthConfigTokenOLD
 *
 * @deprecated since v19
 */
class InstanceAuthConfigTokenOLD
{
    const METHOD = 'token';
    
    /**
     * @param string $token Token that must begin with "secret-token:"
     * @param bool $validate Whether to validate the data automatically
     */
    public function __construct(
        public readonly string $token,
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
        if (empty($this->token)) {
            throw new \InvalidArgumentException('Token cannot be empty');
        }

        if (!str_starts_with($this->token, 'secret-token:')) {
            throw new \InvalidArgumentException('Token must begin with "secret-token:"');
        }
    }

    /**
     * Creates a new instance from an array of data.
     *
     * @param array{
     *     method: "token",
     *     token: string
     * } $data
     * @return self
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            token: $data['token']
        );
    }
}

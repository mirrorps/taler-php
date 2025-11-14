<?php

namespace Taler\Api\TwoFactorAuth\Dto;

/**
 * Request body to solve a TAN challenge.
 *
 * @since v21
 */
class MerchantChallengeSolveRequest
{
    /**
     * @param string $tan The TAN code that solves $CHALLENGE_ID.
     * @param bool $validate Whether to validate inputs
     */
    public function __construct(
        public readonly string $tan,
        bool $validate = true
    ) {
        if ($validate) {
            $this->validate();
        }
    }

    /**
     * @param array{
     *   tan: string
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            tan: $data['tan']
        );
    }

    public function validate(): void
    {
        if ($this->tan === '' || trim($this->tan) === '') {
            throw new \InvalidArgumentException('tan must not be empty');
        }
    }
}




<?php

namespace Taler\Api\Instance\Dto;

/**
 * DTO for Challenge response.
 *
 * Returned when 2FA is required for an operation.
 *
 * @since v21
 */
class Challenge
{
    /**
     * @param string $challenge_id Unique identifier of the challenge to solve to run this protected operation
     */
    public function __construct(
        public readonly string $challenge_id
    ) {}

    /**
     * Creates a new instance from an array of data.
     *
     * @param array{
     *     challenge_id: string
     * } $data
     * @return self
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            challenge_id: $data['challenge_id']
        );
    }

    /**
     * Get the challenge ID.
     *
     * @return string
     */
    public function getChallengeId(): string
    {
        return $this->challenge_id;
    }
}

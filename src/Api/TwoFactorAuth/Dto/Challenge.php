<?php

namespace Taler\Api\TwoFactorAuth\Dto;

/**
 * Challenge to solve for a protected operation.
 *
 * @since v21
 */
class Challenge
{
    /**
     * @param string $challenge_id Unique identifier of the challenge to solve to run this protected operation.
     * @param string $tan_channel Channel of the last successful transmission of the TAN challenge. One of TanChannel constants.
     * @param string $tan_info Info of the last successful transmission of the TAN challenge. Hint to show to the user as to where the challenge was sent or what to use to solve the challenge. May not contain the full address for privacy.
     */
    public function __construct(
        public readonly string $challenge_id,
        public readonly string $tan_channel,
        public readonly string $tan_info,
    ) {
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param array{
     *     challenge_id: string,
     *     tan_channel: string,
     *     tan_info: string
     * } $data
     * @return self
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            challenge_id: $data['challenge_id'],
            tan_channel: $data['tan_channel'],
            tan_info: $data['tan_info']
        );
    }
}



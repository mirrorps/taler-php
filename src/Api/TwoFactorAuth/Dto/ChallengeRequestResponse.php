<?php

namespace Taler\Api\TwoFactorAuth\Dto;

use Taler\Api\Dto\Timestamp;

/**
 * Response to a challenge (TAN) request.
 *
 * Contains timing information about when the challenge must be solved and when
 * a retransmission may be requested.
 *
 * @since v21
 */
class ChallengeRequestResponse
{
    /**
     * @param Timestamp $solve_expiration How long the client has to solve the challenge
     * @param Timestamp $earliest_retransmission Earliest time at which a new transmission may be requested
     */
    public function __construct(
        public readonly Timestamp $solve_expiration,
        public readonly Timestamp $earliest_retransmission,
    ) {
    }

    /**
     * Creates a new instance from an array of data.
     *
     * @param array{
     *     solve_expiration: array{t_s: int|string},
     *     earliest_retransmission: array{t_s: int|string}
     * } $data
     * @return self
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            solve_expiration: Timestamp::fromArray($data['solve_expiration']),
            earliest_retransmission: Timestamp::fromArray($data['earliest_retransmission'])
        );
    }
}




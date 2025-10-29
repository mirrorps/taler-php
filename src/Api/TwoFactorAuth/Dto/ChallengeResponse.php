<?php

namespace Taler\Api\TwoFactorAuth\Dto;

/**
 * Response carrying the list of challenges to solve.
 *
 * @since v21
 */
class ChallengeResponse
{
    /**
     * @param array<int, Challenge> $challenges List of challenge IDs that must be solved before the client may proceed.
     * @param bool $combi_and True if all challenges must be solved (AND), false if it is sufficient to solve one of them (OR).
     */
    public function __construct(
        public readonly array $challenges,
        public readonly bool $combi_and,
    ) {
    }

    /**
     * Creates a new instance from an array of data.
     *
     * @param array{
     *     challenges: array<int, array{challenge_id: string, tan_channel: string, tan_info: string}>,
     *     combi_and: bool
     * } $data
     * @return self
     */
    public static function createFromArray(array $data): self
    {
        $challenges = array_map(
            fn (array $item): Challenge => Challenge::createFromArray($item),
            $data['challenges']
        );

        return new self(
            challenges: $challenges,
            combi_and: $data['combi_and']
        );
    }
}



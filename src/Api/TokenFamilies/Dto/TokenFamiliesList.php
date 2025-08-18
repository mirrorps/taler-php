<?php

namespace Taler\Api\TokenFamilies\Dto;

/**
 * Token families list response DTO.
 *
 * No validation for response DTOs.
 */
class TokenFamiliesList
{
    /**
     * @param array<int, TokenFamilySummary> $token_families
     */
    public function __construct(
        public readonly array $token_families
    ) {
    }

    /**
     * @param array{
     *   token_families: array<int, array{
     *     slug: string,
     *     name: string,
     *     valid_after: array{t_s: int|string},
     *     valid_before: array{t_s: int|string},
     *     kind: string
     *   }>
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        $summaries = array_map(
            static fn(array $d): TokenFamilySummary => TokenFamilySummary::createFromArray($d),
            $data['token_families']
        );

        return new self(token_families: $summaries);
    }
}



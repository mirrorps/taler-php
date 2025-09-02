<?php

namespace Taler\Api\Instance\Dto;

/**
 * DTO for TokenInfos
 *
 * @since v19
 */
class TokenInfos
{
    /**
     * @param array<TokenInfo> $tokens
     */
    public function __construct(
        public readonly array $tokens,
    ) {
    }

    /**
     * Create from array
     *
     * @param array{tokens: array<int, array{creation_time: array{t_s: int|string}, expiration: array{t_s: int|string}, scope: string, refreshable: bool, description?: string|null, serial: int}>} $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            tokens: array_map(
                fn(array $item) => TokenInfo::createFromArray($item),
                $data['tokens']
            )
        );
    }
}



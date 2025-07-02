<?php

namespace Taler\Api\ContractTerms\Dto;

/**
 * DTO for contract output token data
 * 
 * @see https://docs.taler.net/core/api-common.html
 */
class ContractOutputToken
{
    private const TYPE = 'token';

    /**
     * @param string $token_family_slug Slug of the token family in the token_families map on the top-level
     * @param int $key_index Index of the public key for this output token in the ContractTokenFamily keys array
     * @param int|null $count Number of tokens to be issued. Defaults to one if the field is not provided.
     */
    public function __construct(
        public readonly string $token_family_slug,
        public readonly int $key_index,
        public readonly ?int $count = 1,
    ) {
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param array{
     *     token_family_slug: string,
     *     key_index: int,
     *     count?: int|null
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            token_family_slug: $data['token_family_slug'],
            key_index: $data['key_index'],
            count: $data['count'] ?? 1
        );
    }

    /**
     * Get the type of the contract output
     */
    public function getType(): string
    {
        return self::TYPE;
    }
} 
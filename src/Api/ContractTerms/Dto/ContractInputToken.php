<?php

namespace Taler\Api\ContractTerms\Dto;

/**
 * DTO for contract input token data
 * 
 * @see https://docs.taler.net/core/api-common.html
 */
class ContractInputToken
{
    private const TYPE = 'token';

    /**
     * @param string $token_family_slug Slug of the token family in the token_families map on the order
     * @param int|null $count Number of tokens of this type required. Defaults to one if the field is not provided.
     */
    public function __construct(
        public readonly string $token_family_slug,
        public readonly ?int $count = 1,
    ) {
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param array{
     *     token_family_slug: string,
     *     count?: int|null
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            token_family_slug: $data['token_family_slug'],
            count: $data['count'] ?? 1
        );
    }

    /**
     * Get the type of the contract input
     */
    public function getType(): string
    {
        return self::TYPE;
    }
} 
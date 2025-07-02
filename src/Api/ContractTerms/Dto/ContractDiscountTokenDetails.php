<?php

namespace Taler\Api\ContractTerms\Dto;

/**
 * DTO for contract discount token details data
 * 
 * @see https://docs.taler.net/core/api-common.html
 */
class ContractDiscountTokenDetails
{
    private const CLASS_TYPE = 'discount';

    /**
     * @param array<int, string> $expected_domains Array of domain names where this discount token is intended to be used
     */
    public function __construct(
        public readonly array $expected_domains
    ) {
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param array{
     *     expected_domains: array<int, string>
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            expected_domains: $data['expected_domains']
        );
    }

    /**
     * Get the class type of the discount token details
     */
    public function getClass(): string
    {
        return self::CLASS_TYPE;
    }
} 
<?php

namespace Taler\Api\ContractTerms\Dto;

/**
 * DTO for contract subscription token details data
 * 
 * @see https://docs.taler.net/core/api-common.html
 */
class ContractSubscriptionTokenDetails
{
    private const CLASS_TYPE = 'subscription';

    /**
     * @param array<int, string> $trusted_domains Array of domain names where this subscription can be safely used
     */
    public function __construct(
        public readonly array $trusted_domains
    ) {
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param array{
     *     trusted_domains: array<int, string>
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            trusted_domains: $data['trusted_domains']
        );
    }

    /**
     * Get the class type of the subscription token details
     */
    public function getClass(): string
    {
        return self::CLASS_TYPE;
    }
} 
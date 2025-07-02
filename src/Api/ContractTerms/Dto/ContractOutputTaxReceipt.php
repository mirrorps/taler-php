<?php

namespace Taler\Api\ContractTerms\Dto;

/**
 * DTO for contract output tax receipt data
 * 
 * @see https://docs.taler.net/core/api-common.html
 */
class ContractOutputTaxReceipt
{
    private const TYPE = 'tax-receipt';

    /**
     * @param array<int, string> $donau_urls Array of base URLs of donation authorities that can be used to issue the tax receipts
     * @param string $amount Total amount that will be on the tax receipt
     */
    public function __construct(
        public readonly array $donau_urls,
        public readonly string $amount,
    ) {
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param array{
     *     donau_urls: array<int, string>,
     *     amount: string
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            donau_urls: $data['donau_urls'],
            amount: $data['amount']
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
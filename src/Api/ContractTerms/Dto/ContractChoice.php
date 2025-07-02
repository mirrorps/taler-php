<?php

namespace Taler\Api\ContractTerms\Dto;

/**
 * DTO for contract choice data
 * 
 * @see https://docs.taler.net/core/api-common.html
 */
class ContractChoice
{
    /**
     * @param string $amount Price to be paid for this choice. Could be 0. The price is in addition to other instruments.
     * @param array<int, ContractInputToken> $inputs List of inputs the wallet must provision to satisfy the conditions for the contract
     * @param array<int, ContractOutputToken|ContractOutputTaxReceipt> $outputs List of outputs the merchant promises to yield
     * @param string $max_fee Maximum total deposit fee accepted by the merchant for this contract
     */
    public function __construct(
        public readonly string $amount,
        public readonly array $inputs,
        public readonly array $outputs,
        public readonly string $max_fee,
    ) {
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param array{
     *     amount: string,
     *     inputs: array<int, array{
     *         token_family_slug: string,
     *         count?: int|null
     *     }>,
     *     outputs: array<int, array{
     *         token_family_slug?: string,
     *         key_index?: int,
     *         count?: int|null,
     *         donau_urls?: array<int, string>,
     *         amount?: string
     *     }>,
     *     max_fee: string
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        $inputs = array_map(
            fn (array $input) => ContractInputToken::createFromArray($input),
            $data['inputs']
        );

        $outputs = array_map(
            function (array $output) {
                if (isset($output['token_family_slug'], $output['key_index'])) {
                    /** @var array{token_family_slug: string, key_index: int, count?: int|null} $output */
                    return ContractOutputToken::createFromArray($output);
                }
                /** @var array{donau_urls: array<int, string>, amount: string} $output */
                return ContractOutputTaxReceipt::createFromArray($output);
            },
            $data['outputs']
        );

        return new self(
            amount: $data['amount'],
            inputs: $inputs,
            outputs: $outputs,
            max_fee: $data['max_fee']
        );
    }
} 
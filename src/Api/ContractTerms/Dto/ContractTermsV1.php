<?php

namespace Taler\Api\ContractTerms\Dto;

/**
 * DTO for contract terms version 1 data
 * 
 * @see https://docs.taler.net/core/api-common.html
 * @see https://docs.taler.net/design-documents/046-mumimo-contracts.html
 */
class ContractTermsV1
{
    private const VERSION = 1;

    /**
     * @param array<int, ContractChoice> $choices List of contract choices that the customer can select from
     * @param array<string, ContractTokenFamily> $token_families Map of storing metadata and issue keys of token families referenced in this contract
     */
    public function __construct(
        public readonly array $choices,
        public readonly array $token_families
    ) {
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param array{
     *     choices: array<int, array{
     *         amount: string,
     *         inputs: array<int, array{
     *             token_family_slug: string,
     *             count?: int|null
     *         }>,
     *         outputs: array<int, array{
     *             token_family_slug?: string,
     *             key_index?: int,
     *             count?: int|null,
     *             donau_urls?: array<int, string>,
     *             amount?: string
     *         }>,
     *         max_fee: string
     *     }>,
     *     token_families: array<string, array{
     *         name: string,
     *         description: string,
     *         description_i18n?: array<string, string>|null,
     *         keys: array<int, array{
     *             cipher: 'RSA'|'CS',
     *             rsa_pub?: string,
     *             cs_pub?: string,
     *             signature_validity_start: array{t_s: int|string},
     *             signature_validity_end: array{t_s: int|string}
     *         }>,
     *         details: array{
     *             class: 'subscription'|'discount',
     *             trusted_domains?: array<int, string>,
     *             expected_domains?: array<int, string>
     *         },
     *         critical: bool
     *     }>
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        $choices = array_map(
            fn (array $choice) => ContractChoice::createFromArray($choice),
            $data['choices']
        );

        $tokenFamilies = array_map(
            fn (array $family) => ContractTokenFamily::createFromArray($family),
            $data['token_families']
        );

        return new self(
            choices: $choices,
            token_families: $tokenFamilies
        );
    }

    /**
     * Get the version of the contract terms
     */
    public function getVersion(): int
    {
        return self::VERSION;
    }
} 
<?php

namespace Taler\Api\Exchange\Dto;

use Taler\Api\Dto\CurrencySpecification;

/**
 * DTO for exchange version response from the exchange API
 */
class ExchangeVersionResponse
{
    /**
     * @param string $version Libtool-style representation of the Exchange protocol version ("current:revision:age")
     * @param string $name Protocol name, must be "taler-exchange"
     * @param string $currency Currency code supported by this exchange (e.g. "USD" or "EUR")
     * @param CurrencySpecification $currency_specification How wallets should render this currency
     * @param array<int, string> $supported_kyc_requirements Names of supported KYC requirements (deprecated in v24)
     * @param string|null $implementation URN of the implementation (needed to interpret 'revision' in version) (since protocol v18)
     * @param string|null $shopping_url Shopping URL where users may find shops that accept digital cash from this exchange (since protocol v21)
     * @param string|null $aml_spa_dialect Bank-specific dialect for the AML SPA (since protocol v24)
     */
    public function __construct(
        public readonly string $version,
        public readonly string $name,
        public readonly string $currency,
        public readonly CurrencySpecification $currency_specification,
        public readonly array $supported_kyc_requirements,
        public readonly ?string $implementation = null,
        public readonly ?string $shopping_url = null,
        public readonly ?string $aml_spa_dialect = null,
    ) {
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param array{
     *     version: string,
     *     name: string,
     *     currency: string,
     *     currency_specification: array{
     *         name: string,
     *         currency: string,
     *         num_fractional_input_digits: int,
     *         num_fractional_normal_digits: int,
     *         num_fractional_trailing_zero_digits: int,
     *         alt_unit_names: array<numeric-string, string>
     *     },
     *     supported_kyc_requirements: array<int, string>,
     *     implementation?: string|null,
     *     shopping_url?: string|null,
     *     aml_spa_dialect?: string|null
     * } $data
     * @return self
     * @throws \InvalidArgumentException When required data is missing or invalid
     */
    public static function fromArray(array $data): self
    {
        return new self(
            version: $data['version'],
            name: $data['name'],
            currency: $data['currency'],
            currency_specification: CurrencySpecification::fromArray($data['currency_specification']),
            supported_kyc_requirements: $data['supported_kyc_requirements'],
            implementation: $data['implementation'] ?? null,
            shopping_url: $data['shopping_url'] ?? null,
            aml_spa_dialect: $data['aml_spa_dialect'] ?? null
        );
    }
} 
<?php

namespace Taler\Api\Config\Dto;

use Taler\Api\Dto\CurrencySpecification;

/**
 * Merchant version/config response for GET /config
 *
 * Response DTO: do not include validation.
 */
class MerchantVersionResponse
{
    /**
     * @param string $name Protocol name (expected "taler-merchant")
     */
    public readonly string $name;

    /**
     * @param string $version libtool-style version string (current:revision:age)
     * @param string|null $implementation URN of the implementation (since v8)
     * @param string $currency Default currency
     * @param array<string, CurrencySpecification> $currencies Map currency code to spec
     * @param array<int, ExchangeConfigInfo> $exchanges Trusted exchanges (since v6)
     * @param bool $have_self_provisioning Whether self-provisioning is supported (since v21)
     * @param bool $have_donau Whether Donau extension is supported (since v21)
     * @param array<int, string>|null $mandatory_tan_channels Optional mandatory TAN channels (since v21)
     */
    public function __construct(
        public readonly string $version,
        public readonly ?string $implementation,
        public readonly string $currency,
        /** @var array<string, CurrencySpecification> */
        public readonly array $currencies,
        /** @var array<int, ExchangeConfigInfo> */
        public readonly array $exchanges,
        public readonly bool $have_self_provisioning,
        public readonly bool $have_donau,
        public readonly ?array $mandatory_tan_channels = null,
    ) {
        $this->name = 'taler-merchant';
    }

    /**
     * @param array{
     *   version: string,
     *   implementation?: string,
     *   currency: string,
     *   currencies: array<string, array{name: string, currency: string, num_fractional_input_digits: int, num_fractional_normal_digits: int, num_fractional_trailing_zero_digits: int, alt_unit_names: array<numeric-string, string>}>,
     *   exchanges?: array<int, array{base_url: string, currency: string, master_pub: string}>,
     *   have_self_provisioning?: bool,
     *   have_donau?: bool,
     *   mandatory_tan_channels?: array<int, string>
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        $currencies = [];
        foreach ($data['currencies'] as $code => $spec) {
            $currencies[$code] = CurrencySpecification::fromArray($spec);
        }

        $exchanges = array_map(
            static fn(array $e) => ExchangeConfigInfo::createFromArray($e),
            $data['exchanges'] ?? []
        );

        return new self(
            version: $data['version'],
            implementation: $data['implementation'] ?? null,
            currency: $data['currency'],
            currencies: $currencies,
            exchanges: $exchanges,
            have_self_provisioning: $data['have_self_provisioning'] ?? false,
            have_donau: $data['have_donau'] ?? false,
            mandatory_tan_channels: $data['mandatory_tan_channels'] ?? null,
        );
    }
}



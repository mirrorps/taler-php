<?php

namespace Taler\Api\Dto;

/**
 * DTO for currency specification in Taler API responses
 */
class CurrencySpecification
{
    /**
     * @param string $name Name of the currency (e.g. "US Dollar")
     * @param string $currency Code of the currency (deprecated in protocol v18 for exchange, v6 for merchant)
     * @param int $num_fractional_input_digits Number of digits the user may enter after the decimal separator
     * @param int $num_fractional_normal_digits Number of fractional digits to render in normal font and size
     * @param int $num_fractional_trailing_zero_digits Number of fractional digits to render always, if needed by padding with zeros
     * @param array<numeric-string, string> $alt_unit_names Map of powers of 10 (as string keys) to alternative currency names/symbols.
     *                                                      Must always have an entry under "0" that defines the base name.
     *                                                      Example: ["0" => "€", "3" => "k€"] or ["0" => "BTC", "-3" => "mBTC"]
     */
    public function __construct(
        public readonly string $name,
        public readonly string $currency,
        public readonly int $num_fractional_input_digits,
        public readonly int $num_fractional_normal_digits,
        public readonly int $num_fractional_trailing_zero_digits,
        /** @var array<numeric-string, string> */
        public readonly array $alt_unit_names,
    ) {
        $this->validateAltUnitNames();
    }

    /**
     * Validates that alt_unit_names contains required base unit (key "0")
     * 
     * @throws \InvalidArgumentException When alt_unit_names is missing the required "0" key
     */
    private function validateAltUnitNames(): void
    {
        if (!array_key_exists('0', $this->alt_unit_names)) {
            throw new \InvalidArgumentException(
                'alt_unit_names must contain an entry with key "0" that defines the base unit'
            );
        }
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param array{
     *     name: string,
     *     currency: string,
     *     num_fractional_input_digits: int,
     *     num_fractional_normal_digits: int,
     *     num_fractional_trailing_zero_digits: int,
     *     alt_unit_names: array<numeric-string, string>
     * } $data
     * @return self
     * @throws \InvalidArgumentException When required data is missing or invalid
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            currency: $data['currency'],
            num_fractional_input_digits: $data['num_fractional_input_digits'],
            num_fractional_normal_digits: $data['num_fractional_normal_digits'],
            num_fractional_trailing_zero_digits: $data['num_fractional_trailing_zero_digits'],
            alt_unit_names: $data['alt_unit_names']
        );
    }
} 
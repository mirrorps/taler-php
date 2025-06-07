<?php

namespace Taler\Tests\Api\Exchange\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\CurrencySpecification;
use Taler\Api\Exchange\Dto\ExchangeVersionResponse;

class ExchangeVersionResponseTest extends TestCase
{
    /** @var array{
     *     name: string,
     *     currency: string,
     *     num_fractional_input_digits: int,
     *     num_fractional_normal_digits: int,
     *     num_fractional_trailing_zero_digits: int,
     *     alt_unit_names: array<numeric-string, string>
     * }
     */
    private array $validCurrencySpec = [
        'name' => 'US Dollar',
        'currency' => 'USD',
        'num_fractional_input_digits' => 2,
        'num_fractional_normal_digits' => 2,
        'num_fractional_trailing_zero_digits' => 2,
        'alt_unit_names' => ['0' => '$', '3' => 'k$']
    ];

    /** @var array{
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
     *     implementation: string,
     *     shopping_url: string,
     *     aml_spa_dialect: string
     * }
     */
    private array $validData = [
        'version' => '1:2:0',
        'name' => 'taler-exchange',
        'currency' => 'USD',
        'currency_specification' => [
            'name' => 'US Dollar',
            'currency' => 'USD',
            'num_fractional_input_digits' => 2,
            'num_fractional_normal_digits' => 2,
            'num_fractional_trailing_zero_digits' => 2,
            'alt_unit_names' => ['0' => '$', '3' => 'k$']
        ],
        'supported_kyc_requirements' => [0 => 'basic', 1 => 'full'],
        'implementation' => 'urn:taler-exchange:1',
        'shopping_url' => 'https://marketplace.taler.net',
        'aml_spa_dialect' => 'standard'
    ];

    public function testConstructorWithRequiredOnly(): void
    {
        $currencySpec = CurrencySpecification::fromArray($this->validCurrencySpec);
        $response = new ExchangeVersionResponse(
            version: $this->validData['version'],
            name: $this->validData['name'],
            currency: $this->validData['currency'],
            currency_specification: $currencySpec,
            supported_kyc_requirements: $this->validData['supported_kyc_requirements']
        );

        $this->assertSame($this->validData['version'], $response->version);
        $this->assertSame($this->validData['name'], $response->name);
        $this->assertSame($this->validData['currency'], $response->currency);
        $this->assertSame($currencySpec, $response->currency_specification);
        $this->assertSame($this->validData['supported_kyc_requirements'], $response->supported_kyc_requirements);
        $this->assertNull($response->implementation);
        $this->assertNull($response->shopping_url);
        $this->assertNull($response->aml_spa_dialect);
    }

    public function testConstructorWithAllParameters(): void
    {
        $currencySpec = CurrencySpecification::fromArray($this->validCurrencySpec);
        $response = new ExchangeVersionResponse(
            version: $this->validData['version'],
            name: $this->validData['name'],
            currency: $this->validData['currency'],
            currency_specification: $currencySpec,
            supported_kyc_requirements: $this->validData['supported_kyc_requirements'],
            implementation: $this->validData['implementation'],
            shopping_url: $this->validData['shopping_url'],
            aml_spa_dialect: $this->validData['aml_spa_dialect']
        );

        $this->assertSame($this->validData['version'], $response->version);
        $this->assertSame($this->validData['name'], $response->name);
        $this->assertSame($this->validData['currency'], $response->currency);
        $this->assertSame($currencySpec, $response->currency_specification);
        $this->assertSame($this->validData['supported_kyc_requirements'], $response->supported_kyc_requirements);
        $this->assertSame($this->validData['implementation'], $response->implementation);
        $this->assertSame($this->validData['shopping_url'], $response->shopping_url);
        $this->assertSame($this->validData['aml_spa_dialect'], $response->aml_spa_dialect);
    }

    public function testFromArrayWithRequiredOnly(): void
    {
        /** @var array{
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
         *     supported_kyc_requirements: array<int, string>
         * } $data
         */
        $data = [
            'version' => $this->validData['version'],
            'name' => $this->validData['name'],
            'currency' => $this->validData['currency'],
            'currency_specification' => $this->validData['currency_specification'],
            'supported_kyc_requirements' => $this->validData['supported_kyc_requirements']
        ];

        $response = ExchangeVersionResponse::fromArray($data);

        $this->assertSame($data['version'], $response->version);
        $this->assertSame($data['name'], $response->name);
        $this->assertSame($data['currency'], $response->currency);
        $this->assertEquals(
            CurrencySpecification::fromArray($data['currency_specification']), 
            $response->currency_specification
        );
        $this->assertSame($data['supported_kyc_requirements'], $response->supported_kyc_requirements);
        $this->assertNull($response->implementation);
        $this->assertNull($response->shopping_url);
        $this->assertNull($response->aml_spa_dialect);
    }

    public function testFromArrayWithAllParameters(): void
    {
        $response = ExchangeVersionResponse::fromArray($this->validData);

        $this->assertSame($this->validData['version'], $response->version);
        $this->assertSame($this->validData['name'], $response->name);
        $this->assertSame($this->validData['currency'], $response->currency);
        $this->assertEquals(
            CurrencySpecification::fromArray($this->validData['currency_specification']), 
            $response->currency_specification
        );
        $this->assertSame($this->validData['supported_kyc_requirements'], $response->supported_kyc_requirements);
        $this->assertSame($this->validData['implementation'], $response->implementation);
        $this->assertSame($this->validData['shopping_url'], $response->shopping_url);
        $this->assertSame($this->validData['aml_spa_dialect'], $response->aml_spa_dialect);
    }

    public function testFromArrayWithInvalidCurrencySpecification(): void
    {
        /** @var array{
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
         *     implementation: string,
         *     shopping_url: string,
         *     aml_spa_dialect: string
         * } $data
         */
        $data = $this->validData;
        unset($data['currency_specification']['alt_unit_names']['0']); // Remove required base unit

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('alt_unit_names must contain an entry with key "0" that defines the base unit');

        ExchangeVersionResponse::fromArray($data);
    }
} 
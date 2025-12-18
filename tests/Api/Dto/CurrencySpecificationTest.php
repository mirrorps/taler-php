<?php

namespace Taler\Tests\Api\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\CurrencySpecification;

class CurrencySpecificationTest extends TestCase
{
    /**
     * @var array{
     *     name: string,
     *     currency: string,
     *     num_fractional_input_digits: int,
     *     num_fractional_normal_digits: int,
     *     num_fractional_trailing_zero_digits: int,
     *     alt_unit_names: array<int|numeric-string, string>
     * }
     */
    private array $validData = [
        'name' => 'US Dollar',
        'currency' => 'USD',
        'num_fractional_input_digits' => 2,
        'num_fractional_normal_digits' => 2,
        'num_fractional_trailing_zero_digits' => 2,
        'alt_unit_names' => ['0' => '$', '3' => 'k$']
    ];

    public function testConstructorWithValidData(): void
    {
        $spec = new CurrencySpecification(
            name: $this->validData['name'],
            currency: $this->validData['currency'],
            num_fractional_input_digits: $this->validData['num_fractional_input_digits'],
            num_fractional_normal_digits: $this->validData['num_fractional_normal_digits'],
            num_fractional_trailing_zero_digits: $this->validData['num_fractional_trailing_zero_digits'],
            alt_unit_names: $this->validData['alt_unit_names']
        );

        $this->assertSame($this->validData['name'], $spec->name);
        $this->assertSame($this->validData['currency'], $spec->currency);
        $this->assertSame($this->validData['num_fractional_input_digits'], $spec->num_fractional_input_digits);
        $this->assertSame($this->validData['num_fractional_normal_digits'], $spec->num_fractional_normal_digits);
        $this->assertSame($this->validData['num_fractional_trailing_zero_digits'], $spec->num_fractional_trailing_zero_digits);
        $this->assertSame($this->validData['alt_unit_names'], $spec->alt_unit_names);
    }

    public function testConstructorWithMissingBaseUnit(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('alt_unit_names must contain an entry with key "0" that defines the base unit');

        new CurrencySpecification(
            name: $this->validData['name'],
            currency: $this->validData['currency'],
            num_fractional_input_digits: $this->validData['num_fractional_input_digits'],
            num_fractional_normal_digits: $this->validData['num_fractional_normal_digits'],
            num_fractional_trailing_zero_digits: $this->validData['num_fractional_trailing_zero_digits'],
            alt_unit_names: ['3' => 'k$'] // Missing '0' key
        );
    }

    public function testFromArrayWithValidData(): void
    {
        $spec = CurrencySpecification::createFromArray($this->validData);

        $this->assertSame($this->validData['name'], $spec->name);
        $this->assertSame($this->validData['currency'], $spec->currency);
        $this->assertSame($this->validData['num_fractional_input_digits'], $spec->num_fractional_input_digits);
        $this->assertSame($this->validData['num_fractional_normal_digits'], $spec->num_fractional_normal_digits);
        $this->assertSame($this->validData['num_fractional_trailing_zero_digits'], $spec->num_fractional_trailing_zero_digits);
        $this->assertSame($this->validData['alt_unit_names'], $spec->alt_unit_names);
    }

    public function testFromArrayWithMissingBaseUnit(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('alt_unit_names must contain an entry with key "0" that defines the base unit');

        $invalidData = $this->validData;
        $invalidData['alt_unit_names'] = ['3' => 'k$']; // Missing '0' key

        CurrencySpecification::createFromArray($invalidData);
    }
} 
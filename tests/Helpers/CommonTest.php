<?php

namespace Taler\Tests\Helpers;

use PHPUnit\Framework\TestCase;
use function Taler\Helpers\isValidUrl;
use function Taler\Helpers\isValidTalerAmount;

class CommonTest extends TestCase
{
    public function test_is_valid_base_url_accepts_valid_urls(): void
    {
        $this->assertTrue(isValidUrl('https://example.com'));
        $this->assertTrue(isValidUrl('https://api.taler.net'));
        $this->assertTrue(isValidUrl('https://demo.taler.net/instances/sandbox'));
    }

    public function test_is_valid_base_url_rejects_invalid_urls(): void
    {
        $this->assertFalse(isValidUrl('http://example.com')); // non-https
        $this->assertFalse(isValidUrl('not-a-url'));         // invalid format
        $this->assertFalse(isValidUrl('ftp://example.com')); // wrong protocol
        $this->assertFalse(isValidUrl(''));                  // empty string
    }

    /**
     * @dataProvider validTalerAmountProvider
     */
    public function test_is_valid_taler_amount_accepts_valid_amounts(string $amount): void
    {
        $this->assertTrue(isValidTalerAmount($amount), "Expected '$amount' to be valid");
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function validTalerAmountProvider(): array
    {
        return [
            'simple amount' => ['EUR:10'],
            'amount with cents' => ['EUR:1.50'],
            'zero with fraction' => ['USD:0.00'],
            'max fractional digits' => ['CHF:1.12345678'],
            'large integer' => ['EUR:999999999999'],
            'max allowed integer' => ['EUR:4503599627370496'],
            'positive sign' => ['+EUR:5.00'],
            'negative sign' => ['-CHF:3.50'],
            'three char currency' => ['EUR:1'],
            'eleven char currency' => ['ABCDEFGHIJK:1'],
            'mixed case currency' => ['EuRo:10.00'],
            'small fractional' => ['USD:0.00000001'],
        ];
    }

    /**
     * @dataProvider invalidTalerAmountProvider
     */
    public function test_is_valid_taler_amount_rejects_invalid_amounts(string $amount, string $reason): void
    {
        $this->assertFalse(isValidTalerAmount($amount), "Expected '$amount' to be invalid: $reason");
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function invalidTalerAmountProvider(): array
    {
        return [
            'no currency' => ['42', 'missing currency prefix'],
            'no currency with decimal' => ['10.00', 'missing currency prefix'],
            'empty string' => ['', 'empty string'],
            'only currency' => ['EUR:', 'missing value'],
            'only colon' => [':', 'missing currency and value'],
            'trailing dot' => ['EUR:1.', 'trailing dot not allowed'],
            'leading dot' => ['EUR:.1', 'leading dot not allowed'],
            'double colon' => ['EUR:10:00', 'multiple colons'],
            'two char currency' => ['EU:10', 'currency too short'],
            'twelve char currency' => ['ABCDEFGHIJKL:10', 'currency too long'],
            'numeric currency' => ['123:10', 'currency must be letters only'],
            'currency with number' => ['EUR1:10', 'currency must be letters only'],
            'currency with special char' => ['EU-R:10', 'currency must be letters only'],
            'too many decimal digits' => ['EUR:1.123456789', 'more than 8 fractional digits'],
            'integer too large' => ['EUR:4503599627370497', 'integer exceeds 2^52'],
            'very large integer' => ['EUR:99999999999999999', 'integer exceeds 2^52'],
            'negative without currency' => ['-10.00', 'missing currency'],
            'letters in value' => ['EUR:10a', 'value contains non-digits'],
            'multiple dots' => ['EUR:10.00.00', 'multiple decimal points'],
            'space in amount' => ['EUR: 10', 'space not allowed'],
            'whitespace only' => ['   ', 'whitespace only'],
        ];
    }
}
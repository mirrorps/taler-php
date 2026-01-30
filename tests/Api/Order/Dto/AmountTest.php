<?php

namespace Taler\Tests\Api\Order\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Order\Dto\Amount;

class AmountTest extends TestCase
{
    public function testConstructParsesCurrencyAndValue(): void
    {
        $amount = new Amount('EUR:10.50');

        $this->assertSame('EUR', $amount->currency);
        $this->assertSame('10.50', $amount->value);
    }

    public function testCreateFromCurrencyAndValue(): void
    {
        $amount = Amount::createFromCurrencyAndValue('EUR', '10.50');

        $this->assertSame('EUR', $amount->currency);
        $this->assertSame('10.50', $amount->value);
        $this->assertSame('EUR:10.50', (string) $amount);
    }

    public function testToStringReturnsOriginalFormat(): void
    {
        $amount = new Amount('CHF:1.12345678');

        $this->assertSame('CHF:1.12345678', $amount->__toString());
    }

    public function testJsonSerializeReturnsStringAmount(): void
    {
        $amount = new Amount('USD:0.00');

        $this->assertSame('"USD:0.00"', json_encode($amount, JSON_THROW_ON_ERROR));
    }

    public function testConstructRejectsInvalidAmountFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Invalid amount format: '42'. Expected 'CURRENCY:VALUE' (e.g., 'EUR:10.50')"
        );

        new Amount('42');
    }

    public function testPropertiesAreReadonly(): void
    {
        $currencyProp = new \ReflectionProperty(Amount::class, 'currency');
        $valueProp = new \ReflectionProperty(Amount::class, 'value');

        $this->assertTrue($currencyProp->isReadOnly());
        $this->assertTrue($valueProp->isReadOnly());
    }
}


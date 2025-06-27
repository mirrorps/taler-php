<?php

namespace Taler\Tests\Api\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\Tax;

class TaxTest extends TestCase
{
    public function testFromArray(): void
    {
        $data = [
            'name' => 'VAT',
            'tax' => '19.99'
        ];

        $tax = Tax::fromArray($data);

        $this->assertInstanceOf(Tax::class, $tax);
        $this->assertEquals($data['name'], $tax->name);
        $this->assertEquals($data['tax'], $tax->tax);
    }

    public function testFromArrayWithZeroTax(): void
    {
        $data = [
            'name' => 'No Tax',
            'tax' => '0.00'
        ];

        $tax = Tax::fromArray($data);

        $this->assertInstanceOf(Tax::class, $tax);
        $this->assertEquals($data['name'], $tax->name);
        $this->assertEquals($data['tax'], $tax->tax);
    }
} 
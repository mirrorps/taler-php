<?php

namespace Taler\Tests\Api\Order\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Order\Dto\OrderOutputTaxReceipt;

class OrderOutputTaxReceiptTest extends TestCase
{
    public function testConstruct(): void
    {
        $orderOutput = new OrderOutputTaxReceipt();

        $this->assertSame('tax-receipt', $orderOutput->getType());
    }
}
<?php

namespace Taler\Tests\Api\WireTransfers\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\WireTransfers\Dto\TransferDetails;

class TransferDetailsTest extends TestCase
{
    public function testFromArray(): void
    {
        $data = [
            'credit_amount' => 'EUR:10.50',
            'wtid' => 'WTIDX',
            'payto_uri' => 'payto://iban/DE00',
            'exchange_url' => 'https://exchange.example.com',
            'transfer_serial_id' => 42,
            'execution_time' => ['t_s' => 1700000000],
            'verified' => true,
            'confirmed' => null,
            'expected' => false,
        ];

        $details = TransferDetails::createFromArray($data);

        $this->assertSame('EUR:10.50', $details->credit_amount);
        $this->assertSame('WTIDX', $details->wtid);
        $this->assertSame('payto://iban/DE00', $details->payto_uri);
        $this->assertSame('https://exchange.example.com', $details->exchange_url);
        $this->assertSame(42, $details->transfer_serial_id);
        $this->assertSame(1700000000, $details->execution_time->t_s);
        $this->assertTrue($details->verified);
        $this->assertNull($details->confirmed);
        $this->assertFalse($details->expected);
    }
}



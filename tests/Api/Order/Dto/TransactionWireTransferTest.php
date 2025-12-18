<?php

namespace Taler\Tests\Api\Order\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Order\Dto\TransactionWireTransfer;
use Taler\Api\Dto\Timestamp;

class TransactionWireTransferTest extends TestCase
{
    public function testFromArray(): void
    {
        $data = [
            'exchange_url' => 'https://exchange.test.taler.net/',
            'wtid' => 'test_wire_transfer_id',
            'execution_time' => ['t_s' => 1234567890],
            'amount' => '50.00',
            'confirmed' => true
        ];

        $transfer = TransactionWireTransfer::createFromArray($data);

        $this->assertInstanceOf(TransactionWireTransfer::class, $transfer);
        $this->assertEquals($data['exchange_url'], $transfer->exchange_url);
        $this->assertEquals($data['wtid'], $transfer->wtid);
        $this->assertInstanceOf(Timestamp::class, $transfer->execution_time);
        $this->assertEquals($data['execution_time']['t_s'], $transfer->execution_time->t_s);
        $this->assertEquals($data['amount'], $transfer->amount);
        $this->assertEquals($data['confirmed'], $transfer->confirmed);
    }
} 
<?php

namespace Taler\Tests\Api\WireTransfers\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\WireTransfers\Dto\TransferDetails;
use Taler\Api\WireTransfers\Dto\TransfersList;

class TransfersListTest extends TestCase
{
    public function testFromArrayWithMultipleTransfers(): void
    {
        $data = [
            'transfers' => [
                [
                    'credit_amount' => 'EUR:10.00',
                    'wtid' => 'WTID1',
                    'payto_uri' => 'payto://iban/DE00',
                    'exchange_url' => 'https://ex1.example.com',
                    'transfer_serial_id' => 11,
                    'execution_time' => ['t_s' => 1700000000],
                    'verified' => true,
                    'confirmed' => false,
                    'expected' => true,
                ],
                [
                    'credit_amount' => 'EUR:20.00',
                    'wtid' => 'WTID2',
                    'payto_uri' => 'payto://iban/DE01',
                    'exchange_url' => 'https://ex2.example.com',
                    'transfer_serial_id' => 12,
                    'execution_time' => ['t_s' => 1700001000],
                ],
            ],
        ];

        $list = TransfersList::createFromArray($data);

        $this->assertCount(2, $list->transfers);
        $this->assertInstanceOf(TransferDetails::class, $list->transfers[0]);
        $this->assertSame('EUR:10.00', $list->transfers[0]->credit_amount);
        $this->assertSame('WTID2', $list->transfers[1]->wtid);
        $this->assertSame(1700001000, $list->transfers[1]->execution_time->t_s);
    }
}



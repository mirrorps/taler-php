<?php

namespace Taler\Tests\Api\Order\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Order\Dto\TransactionWireReport;

class TransactionWireReportTest extends TestCase
{
    public function testFromArray(): void
    {
        $data = [
            'code' => 1001,
            'hint' => 'Test error description',
            'exchange_code' => 2001,
            'exchange_http_status' => 400,
            'coin_pub' => 'test_coin_pub_key'
        ];

        $report = TransactionWireReport::fromArray($data);

        $this->assertInstanceOf(TransactionWireReport::class, $report);
        $this->assertEquals($data['code'], $report->code);
        $this->assertEquals($data['hint'], $report->hint);
        $this->assertEquals($data['exchange_code'], $report->exchange_code);
        $this->assertEquals($data['exchange_http_status'], $report->exchange_http_status);
        $this->assertEquals($data['coin_pub'], $report->coin_pub);
    }
} 
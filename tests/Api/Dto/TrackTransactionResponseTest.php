<?php

namespace Taler\Tests\Api\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\TrackTransactionResponse;

class TrackTransactionResponseTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = [
            'wtid' => 'base32string',
            'execution_time' => ['t_s' => 1710936000], // 2024-03-20T12:00:00Z as Unix timestamp
            'coin_contribution' => '100',
            'exchange_sig' => 'signature789',
            'exchange_pub' => 'exchange_key456'
        ];

        $response = TrackTransactionResponse::createFromArray($data);

        $this->assertEquals($data['wtid'], $response->getWtid());
        $this->assertEquals($data['execution_time'], ['t_s' => $response->getExecutionTime()->t_s]);
        $this->assertEquals($data['coin_contribution'], $response->getCoinContribution());
        $this->assertEquals($data['exchange_sig'], $response->getExchangeSig());
        $this->assertEquals($data['exchange_pub'], $response->getExchangePub());
    }

    public function testCreateFromArrayWithNeverTimestamp(): void
    {
        $data = [
            'wtid' => 'base32string',
            'execution_time' => ['t_s' => 'never'],
            'coin_contribution' => '100',
            'exchange_sig' => 'signature789',
            'exchange_pub' => 'exchange_key456'
        ];

        $response = TrackTransactionResponse::createFromArray($data);
        $this->assertEquals($data['execution_time'], ['t_s' => $response->getExecutionTime()->t_s]);
    }
} 
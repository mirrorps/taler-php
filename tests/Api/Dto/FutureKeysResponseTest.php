<?php

namespace Taler\Tests\Api\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\FutureKeysResponse;
use Taler\Api\Dto\FutureDenom;
use Taler\Api\Dto\FutureSignKey;

/**
 * Test cases for FutureKeysResponse DTO.
 */
class FutureKeysResponseTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = [
            'future_denoms' => [
                [
                    'section_name' => 'test_section',
                    'value' => '10.00',
                    'stamp_start' => '2024-01-01T00:00:00Z',
                    'stamp_expire_withdraw' => '2024-12-31T23:59:59Z',
                    'stamp_expire_deposit' => '2025-01-31T23:59:59Z',
                    'stamp_expire_legal' => '2025-12-31T23:59:59Z',
                    'denom_pub' => 'test_denom_pub_key',
                    'fee_withdraw' => '0.50',
                    'fee_deposit' => '0.25',
                    'fee_refresh' => '0.10',
                    'fee_refund' => '0.15',
                    'denom_secmod_sig' => 'test_signature'
                ]
            ],
            'future_signkeys' => [
                [
                    'stamp_start' => '2024-01-01T00:00:00Z',
                    'stamp_expire' => '2024-12-31T23:59:59Z',
                    'stamp_expire_legal' => '2025-12-31T23:59:59Z',
                    'key' => 'test_key',
                    'key_secmod_sig' => 'test_signature'
                ]
            ],
            'master_pub' => 'test_master_pub_key',
            'denom_secmod_public_key' => 'test_denom_secmod_key',
            'signkey_secmod_public_key' => 'test_signkey_secmod_key'
        ];

        $response = FutureKeysResponse::createFromArray($data);

        $this->assertInstanceOf(FutureKeysResponse::class, $response);
        
        // Test future_denoms
        $futureDenoms = $response->getFutureDenoms();
        $this->assertCount(1, $futureDenoms);
        $this->assertInstanceOf(FutureDenom::class, $futureDenoms[0]);

        // Test future_signkeys
        $futureSignkeys = $response->getFutureSignkeys();
        $this->assertCount(1, $futureSignkeys);
        $this->assertInstanceOf(FutureSignKey::class, $futureSignkeys[0]);

        // Test scalar properties
        $this->assertEquals($data['master_pub'], $response->getMasterPub());
        $this->assertEquals($data['denom_secmod_public_key'], $response->getDenomSecmodPublicKey());
        $this->assertEquals($data['signkey_secmod_public_key'], $response->getSignkeySecmodPublicKey());
    }
} 
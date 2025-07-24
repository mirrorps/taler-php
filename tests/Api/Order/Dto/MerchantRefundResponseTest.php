<?php

namespace Taler\Tests\Api\Order\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Order\Dto\MerchantRefundResponse;

/**
 * Test cases for MerchantRefundResponse DTO.
 */
final class MerchantRefundResponseTest extends TestCase
{
    /**
     * Test successful creation of MerchantRefundResponse.
     */
    public function testCreateSuccess(): void
    {
        $response = new MerchantRefundResponse(
            taler_refund_uri: 'taler://refund/example',
            h_contract: 'HASH123'
        );

        $this->assertSame('taler://refund/example', $response->taler_refund_uri);
        $this->assertSame('HASH123', $response->h_contract);
    }

    /**
     * Test successful creation from array.
     */
    public function testCreateFromArraySuccess(): void
    {
        $data = [
            'taler_refund_uri' => 'taler://refund/example',
            'h_contract' => 'HASH123'
        ];

        $response = MerchantRefundResponse::createFromArray($data);

        $this->assertSame('taler://refund/example', $response->taler_refund_uri);
        $this->assertSame('HASH123', $response->h_contract);
    }
} 
<?php

namespace Taler\Tests\Api\Order\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\ContractTerms\Dto\ContractTermsV1;
use Taler\Api\Order\Dto\CheckPaymentClaimedResponse;

class CheckPaymentClaimedResponseTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = [
            'order_status' => 'claimed',
            'contract_terms' => [
                'choices' => [
                    [
                        'amount' => '10',
                        'inputs' => [
                            [
                                'token_family_slug' => 'test_token'
                            ]
                        ],
                        'outputs' => [
                            [
                                'token_family_slug' => 'test_token',
                                'key_index' => 1
                            ]
                        ],
                        'max_fee' => '1'
                    ]
                ],
                'token_families' => [
                    'test_token' => [
                        'name' => 'Test Token',
                        'description' => 'Test Description',
                        'keys' => [
                            [
                                'cipher' => 'RSA',
                                'rsa_pub' => 'test_pub_key',
                                'signature_validity_start' => ['t_s' => 123456789],
                                'signature_validity_end' => ['t_s' => 123456789]
                            ]
                        ],
                        'details' => [
                            'class' => 'subscription',
                            'trusted_domains' => ['test.com']
                        ],
                        'critical' => false
                    ]
                ],
                'summary' => 'Test Summary',
                'order_id' => 'test_order_id',
                'products' => [
                    [
                        'description' => 'Test Product'
                    ]
                ],
                'timestamp' => ['t_s' => 123456789],
                'refund_deadline' => ['t_s' => 123456789],
                'pay_deadline' => ['t_s' => 123456789],
                'wire_transfer_deadline' => ['t_s' => 123456789],
                'merchant_pub' => 'test_merchant_pub',
                'merchant_base_url' => 'http://test.com',
                'merchant' => [
                    'name' => 'Test Merchant',
                    'jurisdiction' => [
                        'country' => 'Test Country'
                    ]
                ],
                'h_wire' => 'test_h_wire',
                'wire_method' => 'test_wire_method',
                'exchanges' => [
                    [
                        'url' => 'http://test.com/exchange',
                        'priority' => 1,
                        'master_pub' => 'test_master_pub'
                    ]
                ],
                'nonce' => 'test_nonce'
            ],
            'order_status_url' => 'http://test.com/status'
        ];

        $response = CheckPaymentClaimedResponse::createFromArray($data);

        $this->assertInstanceOf(CheckPaymentClaimedResponse::class, $response);
        $this->assertEquals('claimed', $response->getOrderStatus());
        $this->assertInstanceOf(ContractTermsV1::class, $response->getContractTerms());
        $this->assertEquals('http://test.com/status', $response->getOrderStatusUrl());
    }
} 
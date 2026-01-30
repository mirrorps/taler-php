<?php

namespace Taler\Tests\Api\Order\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\ContractTerms\Dto\ContractTermsV0;
use Taler\Api\ContractTerms\Dto\ContractTermsV1;
use Taler\Api\Dto\Timestamp;
use Taler\Api\Order\Dto\Amount;
use Taler\Api\Order\Dto\CheckPaymentPaidResponse;

class CheckPaymentPaidResponseTest extends TestCase
{
    /**
     * @var array{
     *   summary: string,
     *   order_id: string,
     *   products: array<int, array{
     *     description: string,
     *     product_id?: string|null,
     *     quantity?: int|null,
     *     price?: string|null
     *   }>,
     *   timestamp: array{t_s: int},
     *   refund_deadline: array{t_s: int},
     *   pay_deadline: array{t_s: int},
     *   wire_transfer_deadline: array{t_s: int},
     *   merchant_pub: string,
     *   merchant_base_url: string,
     *   merchant: array{
     *     name: string,
     *     email: string
     *   },
     *   h_wire: string,
     *   wire_method: string,
     *   exchanges: array<int, array{
     *     url: string,
     *     priority: int,
     *     master_pub: string
     *   }>,
     *   nonce: string
     * }
     */
    private array $baseContractTermsData;

    /**
     * @var array{
     *   order_status: 'paid',
     *   refunded: bool,
     *   refund_pending: bool,
     *   wired: bool,
     *   deposit_total: string,
     *   exchange_code: int,
     *   exchange_http_status: int,
     *   refund_amount: string|int,
     *   last_payment: array{t_s: int},
     *   wire_details: array<int, array{
     *     exchange_url: string,
     *     wtid: string,
     *     execution_time: array{t_s: int},
     *     amount: string,
     *     confirmed: bool
     *   }>,
     *   wire_reports: array<int, array{
     *     code: int,
     *     hint: string,
     *     exchange_code: int,
     *     exchange_http_status: int,
     *     coin_pub: string
     *   }>,
     *   refund_details: array<int, array{
     *     reason: string,
     *     pending: bool,
     *     timestamp: array{t_s: int},
     *     amount: string
     *   }>,
     *   order_status_url: string
     * }
     */
    private array $baseResponseData;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup base contract terms data that's common for both V0 and V1
        $this->baseContractTermsData = [
            'amount' => 'EUR:0',
            'max_fee' => 'EUR:0',
            'summary' => 'Test summary',
            'order_id' => 'test-order-123',
            'products' => [
                [
                    'description' => 'Test product',
                    'product_id' => 'prod-123',
                    'quantity' => 1,
                    'price' => 'EUR:10.00',
                ]
            ],
            'timestamp' => ['t_s' => 1234567890],
            'refund_deadline' => ['t_s' => 1234567890],
            'pay_deadline' => ['t_s' => 1234567890],
            'wire_transfer_deadline' => ['t_s' => 1234567890],
            'merchant_pub' => 'test-merchant-pub',
            'merchant_base_url' => 'https://test.merchant.com',
            'merchant' => [
                'name' => 'Test Merchant',
                'email' => 'test@merchant.com',
            ],
            'h_wire' => 'test-h-wire',
            'wire_method' => 'test-wire-method',
            'exchanges' => [
                [
                    'url' => 'https://test.exchange.com',
                    'priority' => 1,
                    'master_pub' => 'test-master-pub',
                ]
            ],
            'nonce' => 'test-nonce',
        ];

        // Setup base response data
        $this->baseResponseData = [
            'order_status' => 'paid',
            'refunded' => false,
            'refund_pending' => false,
            'wired' => true,
            'deposit_total' => 'EUR:10.00',
            'exchange_code' => 200,
            'exchange_http_status' => 200,
            // Docs: 0 when refunded is false.
            'refund_amount' => '0',
            'last_payment' => ['t_s' => 1234567890],
            'wire_details' => [
                [
                    'exchange_url' => 'https://test.exchange.com',
                    'wtid' => 'test-wtid',
                    'execution_time' => ['t_s' => 1234567890],
                    'amount' => 'EUR:10.00',
                    'confirmed' => true,
                ]
            ],
            'wire_reports' => [
                [
                    'code' => 200,
                    'hint' => 'test hint',
                    'exchange_code' => 200,
                    'exchange_http_status' => 200,
                    'coin_pub' => 'test-coin-pub',
                ]
            ],
            'refund_details' => [
                [
                    'reason' => 'test reason',
                    'pending' => false,
                    'timestamp' => ['t_s' => 1234567890],
                    'amount' => 'EUR:0.00',
                ]
            ],
            'order_status_url' => 'https://test.merchant.com/status',
        ];
    }

    public function testCreateFromArrayWithV0ContractTerms(): void
    {
        // Prepare V0 contract terms data
        $contractTermsData = array_merge($this->baseContractTermsData, [
            'version' => 0,
            'amount' => 'EUR:10.00',
            'max_fee' => 'EUR:1.00',
        ]);

        /** @var array{
         *   order_status: 'paid',
         *   refunded: bool,
         *   refund_pending: bool,
         *   wired: bool,
         *   deposit_total: string,
         *   exchange_code: int,
         *   exchange_http_status: int,
         *   refund_amount: string|int,
         *   contract_terms: array{
         *     version?: int|null,
         *     amount?: string,
         *     max_fee?: string,
         *     summary: string,
         *     order_id: string,
         *     products: array<int, array{
         *       description: string,
         *       product_id?: string|null,
         *       description_i18n?: array<string, string>|null,
         *       quantity?: int|null,
         *       unit?: string|null,
         *       price?: string|null,
         *       image?: string|null,
         *       taxes?: array<int, array{name: string, tax: string}>|null
         *     }>,
         *     timestamp: array{t_s: int},
         *     refund_deadline: array{t_s: int},
         *     pay_deadline: array{t_s: int},
         *     wire_transfer_deadline: array{t_s: int},
         *     merchant_pub: string,
         *     merchant_base_url: string,
         *     merchant: array{name: string, email: string},
         *     h_wire: string,
         *     wire_method: string,
         *     exchanges: array<int, array{url: string, priority: int, master_pub: string}>,
         *     nonce: string
         *   },
         *   choice_index?: int|null,
         *   last_payment: array{t_s: int},
         *   wire_details: array<int, array{
         *     exchange_url: string,
         *     wtid: string,
         *     execution_time: array{t_s: int},
         *     amount: string,
         *     confirmed: bool
         *   }>,
         *   wire_reports: array<int, array{
         *     code: int,
         *     hint: string,
         *     exchange_code: int,
         *     exchange_http_status: int,
         *     coin_pub: string
         *   }>,
         *   refund_details: array<int, array{
         *     reason: string,
         *     pending: bool,
         *     timestamp: array{t_s: int},
         *     amount: string
         *   }>,
         *   order_status_url: string
         * } $responseData */
        $responseData = array_merge($this->baseResponseData, [
            'contract_terms' => $contractTermsData,
        ]);

        $response = CheckPaymentPaidResponse::createFromArray($responseData);

        $this->assertInstanceOf(CheckPaymentPaidResponse::class, $response);
        $this->assertInstanceOf(ContractTermsV0::class, $response->contract_terms);
        $this->assertEquals('paid', $response->order_status);
        $this->assertInstanceOf(Amount::class, $response->deposit_total);
        $this->assertSame('EUR:10.00', (string) $response->deposit_total);
        $this->assertEquals(200, $response->exchange_code);
        $this->assertEquals(200, $response->exchange_http_status);
        // $this->assertInstanceOf(Amount::class, $response->refund_amount);
        // $this->assertSame('EUR:0.00', (string) $response->refund_amount);
        $this->assertInstanceOf(Timestamp::class, $response->last_payment);
    }

    public function testCreateFromArrayWithV1ContractTerms(): void
    {
        // Prepare V1 contract terms data
        $contractTermsData = array_merge($this->baseContractTermsData, [
            'version' => 1,
            'choices' => [
                [
                    'amount' => 'EUR:10.00',
                    'inputs' => [
                        [
                            'token_family_slug' => 'test-token',
                            'count' => 1,
                        ]
                    ],
                    'outputs' => [
                        [
                            'token_family_slug' => 'test-token',
                            'key_index' => 1,
                            'count' => 1,
                            'amount' => 'EUR:10.00',
                        ]
                    ],
                    'max_fee' => 'EUR:1.00',
                ]
            ],
            'token_families' => [
                'test-token' => [
                    'name' => 'Test Token',
                    'description' => 'Test token description',
                    'description_i18n' => [
                        'en' => 'Test token description in English',
                    ],
                    'keys' => [
                        [
                            'cipher' => 'CS',
                            'cs_pub' => 'test-cs-pub',
                            'signature_validity_start' => ['t_s' => 1234567890],
                            'signature_validity_end' => ['t_s' => 1234567890],
                        ]
                    ],
                    'details' => [
                        'class' => 'discount',
                        'trusted_domains' => ['test.merchant.com'],
                        'expected_domains' => ['test.merchant.com'],
                    ],
                    'critical' => true,
                ]
            ],
        ]);

        /** @var array{
         *   order_status: 'paid',
         *   refunded: bool,
         *   refund_pending: bool,
         *   wired: bool,
         *   deposit_total: string,
         *   exchange_code: int,
         *   exchange_http_status: int,
         *   refund_amount: string|int,
         *   contract_terms: array{
         *     version: 1,
         *     choices: array<int, array{
         *       amount: string,
         *       inputs: array<int, array{token_family_slug: string, count?: int|null}>,
         *       outputs: array<int, array{token_family_slug?: string, key_index?: int, count?: int|null, donau_urls?: array<int, string>, amount?: string}>,
         *       max_fee: string
         *     }>,
         *     token_families: array<string, array{
         *       name: string,
         *       description: string,
         *       description_i18n?: array<string, string>|null,
         *       keys: array<int, array{
         *         cipher: 'CS'|'RSA',
         *         rsa_pub?: string,
         *         cs_pub?: string,
         *         signature_validity_start: array{t_s: int|string},
         *         signature_validity_end: array{t_s: int|string}
         *       }>,
         *       details: array{
         *         class: 'discount'|'subscription',
         *         trusted_domains?: array<int, string>,
         *         expected_domains?: array<int, string>
         *       },
         *       critical: bool
         *     }>,
         *     summary: string,
         *     order_id: string,
         *     products: array<int, array{
         *       description: string,
         *       product_id?: string|null,
         *       description_i18n?: array<string, string>|null,
         *       quantity?: int|null,
         *       unit?: string|null,
         *       price?: string|null,
         *       image?: string|null,
         *       taxes?: array<int, array{name: string, tax: string}>|null
         *     }>,
         *     timestamp: array{t_s: int},
         *     refund_deadline: array{t_s: int},
         *     pay_deadline: array{t_s: int},
         *     wire_transfer_deadline: array{t_s: int},
         *     merchant_pub: string,
         *     merchant_base_url: string,
         *     merchant: array{name: string, email: string},
         *     h_wire: string,
         *     wire_method: string,
         *     exchanges: array<int, array{url: string, priority: int, master_pub: string}>,
         *     nonce: string
         *   },
         *   choice_index?: int|null,
         *   last_payment: array{t_s: int},
         *   wire_details: array<int, array{
         *     exchange_url: string,
         *     wtid: string,
         *     execution_time: array{t_s: int},
         *     amount: string,
         *     confirmed: bool
         *   }>,
         *   wire_reports: array<int, array{
         *     code: int,
         *     hint: string,
         *     exchange_code: int,
         *     exchange_http_status: int,
         *     coin_pub: string
         *   }>,
         *   refund_details: array<int, array{
         *     reason: string,
         *     pending: bool,
         *     timestamp: array{t_s: int},
         *     amount: string
         *   }>,
         *   order_status_url: string
         * } $responseData */
        $responseData = array_merge($this->baseResponseData, [
            'contract_terms' => $contractTermsData,
            'choice_index' => 0,
        ]);

        $response = CheckPaymentPaidResponse::createFromArray($responseData);

        $this->assertInstanceOf(CheckPaymentPaidResponse::class, $response);
        $this->assertInstanceOf(ContractTermsV1::class, $response->contract_terms);
        $this->assertEquals('paid', $response->order_status);
        $this->assertInstanceOf(Amount::class, $response->deposit_total);
        $this->assertSame('EUR:10.00', (string) $response->deposit_total);
        $this->assertEquals(0, $response->choice_index);
        $this->assertCount(1, $response->wire_details);
        $this->assertCount(1, $response->wire_reports);
        $this->assertCount(1, $response->refund_details);
    }

    public function testCreateFromArrayWithDefaultV0ContractTerms(): void
    {
        // Test with minimal contract terms data (should default to V0)
        $contractTermsData = $this->baseContractTermsData;

        /** @var array{
         *   order_status: 'paid',
         *   refunded: bool,
         *   refund_pending: bool,
         *   wired: bool,
         *   deposit_total: string,
         *   exchange_code: int,
         *   exchange_http_status: int,
         *   refund_amount: string|int,
         *   contract_terms: array{
         *     summary: string,
         *     order_id: string,
         *     products: array<int, array{
         *       description: string,
         *       product_id?: string|null,
         *       description_i18n?: array<string, string>|null,
         *       quantity?: int|null,
         *       unit?: string|null,
         *       price?: string|null,
         *       image?: string|null,
         *       taxes?: array<int, array{name: string, tax: string}>|null
         *     }>,
         *     timestamp: array{t_s: int},
         *     refund_deadline: array{t_s: int},
         *     pay_deadline: array{t_s: int},
         *     wire_transfer_deadline: array{t_s: int},
         *     merchant_pub: string,
         *     merchant_base_url: string,
         *     merchant: array{name: string, email: string},
         *     h_wire: string,
         *     wire_method: string,
         *     exchanges: array<int, array{url: string, priority: int, master_pub: string}>,
         *     nonce: string
         *   },
         *   choice_index?: int|null,
         *   last_payment: array{t_s: int},
         *   wire_details: array<int, array{
         *     exchange_url: string,
         *     wtid: string,
         *     execution_time: array{t_s: int},
         *     amount: string,
         *     confirmed: bool
         *   }>,
         *   wire_reports: array<int, array{
         *     code: int,
         *     hint: string,
         *     exchange_code: int,
         *     exchange_http_status: int,
         *     coin_pub: string
         *   }>,
         *   refund_details: array<int, array{
         *     reason: string,
         *     pending: bool,
         *     timestamp: array{t_s: int},
         *     amount: string
         *   }>,
         *   order_status_url: string
         * } $responseData */
        $responseData = array_merge($this->baseResponseData, [
            'contract_terms' => $contractTermsData,
        ]);

        $response = CheckPaymentPaidResponse::createFromArray($responseData);

        $this->assertInstanceOf(CheckPaymentPaidResponse::class, $response);
        $this->assertInstanceOf(ContractTermsV0::class, $response->contract_terms);
        $this->assertInstanceOf(Amount::class, $response->contract_terms->amount);
        $this->assertEquals('EUR:0', (string) $response->contract_terms->amount);
        $this->assertEquals('EUR:0', $response->contract_terms->max_fee);
    }

    public function testCreateFromArrayWithInvalidVersionDefaultsToV0(): void
    {
        // Test with invalid version (should default to V0)
        $contractTermsData = array_merge($this->baseContractTermsData, [
            'version' => 999,
        ]);

        /** @var array{
         *   order_status: 'paid',
         *   refunded: bool,
         *   refund_pending: bool,
         *   wired: bool,
         *   deposit_total: string,
         *   exchange_code: int,
         *   exchange_http_status: int,
         *   refund_amount: string|int,
         *   contract_terms: array{
         *     version: int,
         *     summary: string,
         *     order_id: string,
         *     products: array<int, array{
         *       description: string,
         *       product_id?: string|null,
         *       description_i18n?: array<string, string>|null,
         *       quantity?: int|null,
         *       unit?: string|null,
         *       price?: string|null,
         *       image?: string|null,
         *       taxes?: array<int, array{name: string, tax: string}>|null
         *     }>,
         *     timestamp: array{t_s: int},
         *     refund_deadline: array{t_s: int},
         *     pay_deadline: array{t_s: int},
         *     wire_transfer_deadline: array{t_s: int},
         *     merchant_pub: string,
         *     merchant_base_url: string,
         *     merchant: array{name: string, email: string},
         *     h_wire: string,
         *     wire_method: string,
         *     exchanges: array<int, array{url: string, priority: int, master_pub: string}>,
         *     nonce: string
         *   },
         *   choice_index?: int|null,
         *   last_payment: array{t_s: int},
         *   wire_details: array<int, array{
         *     exchange_url: string,
         *     wtid: string,
         *     execution_time: array{t_s: int},
         *     amount: string,
         *     confirmed: bool
         *   }>,
         *   wire_reports: array<int, array{
         *     code: int,
         *     hint: string,
         *     exchange_code: int,
         *     exchange_http_status: int,
         *     coin_pub: string
         *   }>,
         *   refund_details: array<int, array{
         *     reason: string,
         *     pending: bool,
         *     timestamp: array{t_s: int},
         *     amount: string
         *   }>,
         *   order_status_url: string
         * } $responseData */
        $responseData = array_merge($this->baseResponseData, [
            'contract_terms' => $contractTermsData,
        ]);

        $response = CheckPaymentPaidResponse::createFromArray($responseData);

        $this->assertInstanceOf(CheckPaymentPaidResponse::class, $response);
        $this->assertInstanceOf(ContractTermsV0::class, $response->contract_terms);
    }
} 
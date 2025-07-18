<?php

namespace Taler\Api\Order\Dto;

use Taler\Api\ContractTerms\Dto\ContractTermsCommon;
use Taler\Api\ContractTerms\Dto\ContractTermsV0;
use Taler\Api\ContractTerms\Dto\ContractTermsV1;
use Taler\Api\Dto\Timestamp;

/**
 * DTO for check payment paid response
 * 
 * @see https://docs.taler.net/core/api-merchant.html#tsref-type-CheckPaymentPaidResponse
 * 
 * @phpstan-type CheckPaymentPaidResponseArray array{
 *   order_status: "paid",
 *   refunded: bool,
 *   refund_pending: bool,
 *   wired: bool,
 *   deposit_total: string,
 *   exchange_code: int,
 *   exchange_http_status: int,
 *   refund_amount: string,
 *   contract_terms: array{
 *     version?: int|null,
 *     amount?: string,
 *     max_fee?: string,
 *     choices?: array<int, array{
 *       amount: string,
 *       inputs: array<int, array{
 *         token_family_slug: string,
 *         count?: int|null
 *       }>,
 *       outputs: array<int, array{
 *         token_family_slug?: string,
 *         key_index?: int,
 *         count?: int|null,
 *         donau_urls?: array<int, string>,
 *         amount?: string
 *       }>,
 *       max_fee: string
 *     }>,
 *     token_families?: array<string, array{
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
 *         class: 'subscription'|'discount',
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
 *       taxes?: array<int, array{name: string, tax: string}>|null,
 *       delivery_date?: array{t_s: int|string}|null
 *     }>,
 *     timestamp: array{t_s: int|string},
 *     refund_deadline: array{t_s: int|string},
 *     pay_deadline: array{t_s: int|string},
 *     wire_transfer_deadline: array{t_s: int|string},
 *     merchant_pub: string,
 *     merchant_base_url: string,
 *     merchant: array{
 *       name: string,
 *       email?: string|null,
 *       website?: string|null,
 *       logo?: string|null,
 *       address?: array{
 *         country?: string|null,
 *         town?: string|null,
 *         state?: string|null,
 *         region?: string|null,
 *         province?: string|null,
 *         street?: string|null
 *       }|null,
 *       jurisdiction?: array{
 *         country?: string|null,
 *         town?: string|null,
 *         state?: string|null,
 *         region?: string|null,
 *         province?: string|null,
 *         street?: string|null
 *       }|null
 *     },
 *     h_wire: string,
 *     wire_method: string,
 *     exchanges: array<int, array{
 *       url: string,
 *       priority: int,
 *       master_pub: string,
 *       max_contribution?: string|null
 *     }>,
 *     nonce: string,
 *     summary_i18n?: array<string, string>|null,
 *     public_reorder_url?: string|null,
 *     fulfillment_url?: string|null,
 *     fulfillment_message?: string|null,
 *     fulfillment_message_i18n?: array<string, string>|null,
 *     delivery_location?: array{
 *       country?: string|null,
 *       town?: string|null,
 *       state?: string|null,
 *       region?: string|null,
 *       province?: string|null,
 *       street?: string|null
 *     }|null,
 *     delivery_date?: array{t_s: int|string}|null,
 *     auto_refund?: array{d_us: int|string}|null,
 *     extra?: object|null,
 *     minimum_age?: int|null
 *   },
 *   choice_index?: int,
 *   last_payment: array{t_s: int|string},
 *   wire_details: array<array{
 *     exchange_url: string,
 *     wtid: string,
 *     execution_time: array{t_s: int},
 *     amount: string,
 *     confirmed: bool
 *   }>,
 *   wire_reports: array<array{
 *     code: int,
 *     hint: string,
 *     exchange_code: int,
 *     exchange_http_status: int,
 *     coin_pub: string
 *   }>,
 *   refund_details: array<array{
 *     reason: string,
 *     pending: bool,
 *     timestamp: array{t_s: int},
 *     amount: string
 *   }>,
 *   order_status_url: string
 * }
 */
class CheckPaymentPaidResponse
{
    /**
     * @param string $order_status The customer paid for this contract (always "paid")
     * @param bool $refunded Was the payment refunded (even partially)?
     * @param bool $refund_pending True if there are any approved refunds that the wallet has not yet obtained
     * @param bool $wired Did the exchange wire us the funds?
     * @param string $deposit_total Total amount the exchange deposited into our bank account for this contract, excluding fees
     * @param int $exchange_code Numeric error code indicating errors the exchange encountered tracking the wire transfer for this purchase
     * @param int $exchange_http_status HTTP status code returned by the exchange when we asked for information to track the wire transfer
     * @param string $refund_amount Total amount that was refunded, 0 if refunded is false
     * @param ContractTermsV0|ContractTermsV1 $contract_terms Contract terms
     * @param Timestamp $last_payment If the order is paid, set to the last time when a payment was made to pay for this order
     * @param array<TransactionWireTransfer> $wire_details The wire transfer status from the exchange for this order if available
     * @param array<TransactionWireReport> $wire_reports Reports about trouble obtaining wire transfer details
     * @param array<RefundDetails> $refund_details The refund details for this order
     * @param string $order_status_url Status URL, can be used as a redirect target for the browser
     * @param int|null $choice_index Index of the selected choice within the choices array of contract terms
     */
    public function __construct(
        public readonly string $order_status,
        public readonly bool $refunded,
        public readonly bool $refund_pending,
        public readonly bool $wired,
        public readonly string $deposit_total,
        public readonly int $exchange_code,
        public readonly int $exchange_http_status,
        public readonly string $refund_amount,
        public readonly ContractTermsV0|ContractTermsV1 $contract_terms,
        public readonly Timestamp $last_payment,
        public readonly array $wire_details,
        public readonly array $wire_reports,
        public readonly array $refund_details,
        public readonly string $order_status_url,
        public readonly ?int $choice_index = null,
    ) {
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param CheckPaymentPaidResponseArray $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            order_status: $data['order_status'],
            refunded: $data['refunded'],
            refund_pending: $data['refund_pending'],
            wired: $data['wired'],
            deposit_total: $data['deposit_total'],
            exchange_code: $data['exchange_code'],
            exchange_http_status: $data['exchange_http_status'],
            refund_amount: $data['refund_amount'],
            contract_terms: self::createContractTerms($data['contract_terms']),
            last_payment: Timestamp::fromArray($data['last_payment']),
            wire_details: array_map(
                static fn (array $detail) => TransactionWireTransfer::fromArray($detail),
                $data['wire_details']
            ),
            wire_reports: array_map(
                static fn (array $report) => TransactionWireReport::fromArray($report),
                $data['wire_reports']
            ),
            refund_details: array_map(
                static fn (array $detail) => RefundDetails::fromArray($detail),
                $data['refund_details']
            ),
            order_status_url: $data['order_status_url'],
            choice_index: $data['choice_index'] ?? null,
        );
    }

    /**
     * Creates the appropriate contract terms instance based on version
     * 
     * @param array{
     *   version?: int|null,
     *   amount?: string,
     *   max_fee?: string,
     *   choices?: array<int, array{
     *     amount: string,
     *     inputs: array<int, array{
     *       token_family_slug: string,
     *       count?: int|null
     *     }>,
     *     outputs: array<int, array{
     *       token_family_slug?: string,
     *       key_index?: int,
     *       count?: int|null,
     *       donau_urls?: array<int, string>,
     *       amount?: string
     *     }>,
     *     max_fee: string
     *   }>,
     *   token_families?: array<string, array{
     *     name: string,
     *     description: string,
     *     description_i18n?: array<string, string>|null,
     *     keys: array<int, array{
     *       cipher: 'CS'|'RSA',
     *       rsa_pub?: string,
     *       cs_pub?: string,
     *       signature_validity_start: array{t_s: int|string},
     *       signature_validity_end: array{t_s: int|string}
     *     }>,
     *     details: array{
     *       class: 'subscription'|'discount',
     *       trusted_domains?: array<int, string>,
     *       expected_domains?: array<int, string>
     *     },
     *     critical: bool
     *   }>,
     *   summary: string,
     *   order_id: string,
     *   products: array<int, array{
     *     description: string,
     *     product_id?: string|null,
     *     description_i18n?: array<string, string>|null,
     *     quantity?: int|null,
     *     unit?: string|null,
     *     price?: string|null,
     *     image?: string|null,
     *     taxes?: array<int, array{name: string, tax: string}>|null,
     *     delivery_date?: array{t_s: int|string}|null
     *   }>,
     *   timestamp: array{t_s: int|string},
     *   refund_deadline: array{t_s: int|string},
     *   pay_deadline: array{t_s: int|string},
     *   wire_transfer_deadline: array{t_s: int|string},
     *   merchant_pub: string,
     *   merchant_base_url: string,
     *   merchant: array{
     *     name: string,
     *     email?: string|null,
     *     website?: string|null,
     *     logo?: string|null,
     *     address?: array{
     *       country?: string|null,
     *       town?: string|null,
     *       state?: string|null,
     *       region?: string|null,
     *       province?: string|null,
     *       street?: string|null
     *     }|null,
     *     jurisdiction?: array{
     *       country?: string|null,
     *       town?: string|null,
     *       state?: string|null,
     *       region?: string|null,
     *       province?: string|null,
     *       street?: string|null
     *     }|null
     *   },
     *   h_wire: string,
     *   wire_method: string,
     *   exchanges: array<int, array{
     *     url: string,
     *     priority: int,
     *     master_pub: string,
     *     max_contribution?: string|null
     *   }>,
     *   nonce: string,
     *   summary_i18n?: array<string, string>|null,
     *   public_reorder_url?: string|null,
     *   fulfillment_url?: string|null,
     *   fulfillment_message?: string|null,
     *   fulfillment_message_i18n?: array<string, string>|null,
     *   delivery_location?: array{
     *     country?: string|null,
     *     town?: string|null,
     *     state?: string|null,
     *     region?: string|null,
     *     province?: string|null,
     *     street?: string|null
     *   }|null,
     *   delivery_date?: array{t_s: int|string}|null,
     *   auto_refund?: array{d_us: int|string}|null,
     *   extra?: object|null,
     *   minimum_age?: int|null
     * } $contractTermsData
     */
    private static function createContractTerms(array $contractTermsData): ContractTermsV0|ContractTermsV1
    {
        $version = $contractTermsData['version'] ?? 0;

        // For version 1, we need choices and token_families
        if ($version === 1 && isset($contractTermsData['choices'], $contractTermsData['token_families'])) {
            /** @var array{choices: array<int, array{amount: string, inputs: array<int, array{token_family_slug: string, count?: int|null}>, outputs: array<int, array{token_family_slug?: string, key_index?: int, count?: int|null, donau_urls?: array<int, string>, amount?: string}>, max_fee: string}>, token_families: array<string, array{name: string, description: string, description_i18n?: array<string, string>|null, keys: array<int, array{cipher: 'CS'|'RSA', rsa_pub?: string, cs_pub?: string, signature_validity_start: array{t_s: int|string}, signature_validity_end: array{t_s: int|string}}>, details: array{class: 'subscription'|'discount', trusted_domains?: array<int, string>, expected_domains?: array<int, string>}, critical: bool}>, summary: string, order_id: string, products: array<int, array{description: string, product_id?: string|null, description_i18n?: array<string, string>|null, quantity?: int|null, unit?: string|null, price?: string|null, image?: string|null, taxes?: array<int, array{name: string, tax: string}>|null}>, timestamp: array{t_s: int|string}, refund_deadline: array{t_s: int|string}, pay_deadline: array{t_s: int|string}, wire_transfer_deadline: array{t_s: int|string}, merchant_pub: string, merchant_base_url: string, merchant: array{name: string, email?: string|null, website?: string|null, logo?: string|null, address?: array{country?: string|null, town?: string|null, state?: string|null, region?: string|null, province?: string|null, street?: string|null}|null, jurisdiction?: array{country?: string|null, town?: string|null, state?: string|null, region?: string|null, province?: string|null, street?: string|null}|null}, h_wire: string, wire_method: string, exchanges: array<int, array{url: string, priority: int, master_pub: string, max_contribution?: string|null}>, nonce: string} $v1Data */
            $v1Data = $contractTermsData;
            return ContractTermsV1::createFromArray($v1Data);
        }

        // For version 0 or default, ensure required fields are present
        $v0Data = array_merge([
            'amount' => '0',
            'max_fee' => '0',
            'summary' => $contractTermsData['summary'],
            'order_id' => $contractTermsData['order_id'],
            'products' => $contractTermsData['products'],
            'timestamp' => $contractTermsData['timestamp'],
            'refund_deadline' => $contractTermsData['refund_deadline'],
            'pay_deadline' => $contractTermsData['pay_deadline'],
            'wire_transfer_deadline' => $contractTermsData['wire_transfer_deadline'],
            'merchant_pub' => $contractTermsData['merchant_pub'],
            'merchant_base_url' => $contractTermsData['merchant_base_url'],
            'merchant' => $contractTermsData['merchant'],
            'h_wire' => $contractTermsData['h_wire'],
            'wire_method' => $contractTermsData['wire_method'],
            'exchanges' => $contractTermsData['exchanges'],
            'nonce' => $contractTermsData['nonce'],
        ], $contractTermsData);

        /** @var array{amount: string, max_fee: string, summary: string, order_id: string, products: array<int, array{description: string, product_id?: string|null, description_i18n?: array<string, string>|null, quantity?: int|null, unit?: string|null, price?: string|null, image?: string|null, taxes?: array<int, array{name: string, tax: string}>|null}>, timestamp: array{t_s: int|string}, refund_deadline: array{t_s: int|string}, pay_deadline: array{t_s: int|string}, wire_transfer_deadline: array{t_s: int|string}, merchant_pub: string, merchant_base_url: string, merchant: array{name: string, email?: string|null, website?: string|null, logo?: string|null, address?: array{country?: string|null, town?: string|null, state?: string|null, region?: string|null, province?: string|null, street?: string|null}|null, jurisdiction?: array{country?: string|null, town?: string|null, state?: string|null, region?: string|null, province?: string|null, street?: string|null}|null}, h_wire: string, wire_method: string, exchanges: array<int, array{url: string, priority: int, master_pub: string, max_contribution?: string|null}>, nonce: string} $data */
        $data = $v0Data;
        return ContractTermsV0::createFromArray($data);
    }
} 
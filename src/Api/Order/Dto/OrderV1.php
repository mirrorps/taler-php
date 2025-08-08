<?php

namespace Taler\Api\Order\Dto;

use Taler\Api\Dto\Location;
use Taler\Api\Dto\RelativeTime;
use Taler\Api\Dto\Timestamp;
use Taler\Api\Inventory\Dto\Product;
use Taler\Api\Order\Dto\OrderChoice;

/**
 * OrderV1 DTO
 *
 * Version 1 order supports discounts and subscriptions.
 * @see https://docs.taler.net/design-documents/046-mumimo-contracts.html
 */
class OrderV1
{
    /**
     * @param int $version Version of the order; must be exactly 1
     * @param array<int, OrderChoice>|null $choices List of contract choices that the customer can select from
     * @param string $summary Human-readable description of the whole purchase
     * @param array<string, string>|null $summary_i18n Map from IETF BCP 47 language tags to localized summaries
     * @param string|null $order_id Unique identifier for the order
     * @param string|null $public_reorder_url URL where the same contract could be ordered again
     * @param string|null $fulfillment_url Fulfillment URL; see ContractTerms
     * @param string|null $fulfillment_message Fulfillment message; see ContractTerms
     * @param array<string, string>|null $fulfillment_message_i18n Map from IETF BCP 47 language tags to localized fulfillment messages
     * @param int|null $minimum_age Minimum age the buyer must have to buy
     * @param array<int, Product>|null $products List of products that are part of the purchase
     * @param Timestamp|null $timestamp Time when this contract was generated
     * @param Timestamp|null $refund_deadline After this deadline has passed, no refunds will be accepted
     * @param Timestamp|null $pay_deadline After this deadline, the merchant won't accept payments for the contract
     * @param Timestamp|null $wire_transfer_deadline Transfer deadline for the exchange
     * @param string|null $merchant_base_url Base URL of the (public!) merchant backend API
     * @param Location|null $delivery_location Delivery location for (all!) products
     * @param Timestamp|null $delivery_date Time indicating when the order should be delivered
     * @param RelativeTime|null $auto_refund Specifies how long the wallet should try to get an automatic refund
     * @param object|null $extra Extra data only interpreted by the merchant frontend
     * @param bool $validate Whether to validate the data upon construction
     */
    public function __construct(
        public readonly int $version,
        public readonly ?array $choices = null,
        public readonly string $summary = '',
        public readonly ?array $summary_i18n = null,
        public readonly ?string $order_id = null,
        public readonly ?string $public_reorder_url = null,
        public readonly ?string $fulfillment_url = null,
        public readonly ?string $fulfillment_message = null,
        public readonly ?array $fulfillment_message_i18n = null,
        public readonly ?int $minimum_age = null,
        public readonly ?array $products = null,
        public readonly ?Timestamp $timestamp = null,
        public readonly ?Timestamp $refund_deadline = null,
        public readonly ?Timestamp $pay_deadline = null,
        public readonly ?Timestamp $wire_transfer_deadline = null,
        public readonly ?string $merchant_base_url = null,
        public readonly ?Location $delivery_location = null,
        public readonly ?Timestamp $delivery_date = null,
        public readonly ?RelativeTime $auto_refund = null,
        public readonly ?object $extra = null,
        bool $validate = true
    ) {
        if ($validate) {
            $this->validate();
        }
    }

    /**
     * Validates only required variables
     */
    public function validate(): void
    {
        if ($this->version !== 1) {
            throw new \InvalidArgumentException('Version must be 1');
        }

        if ($this->summary === '') {
            throw new \InvalidArgumentException('Summary must be a non-empty string');
        }
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param array{
     *   version: int,
     *   choices?: array<int, array{
     *     amount: string,
     *     inputs?: array<int, array{token_family_slug: string, count?: int|null}>,
     *     outputs?: array<int, (array{type: 'token', token_family_slug: string, count?: int|null, valid_at?: array{t_s: int|string}}|array{type: 'tax-receipt'})>,
     *     max_fee?: string
     *   }>,
     *   summary: string,
     *   summary_i18n?: array<string, string>,
     *   order_id?: string,
     *   public_reorder_url?: string,
     *   fulfillment_url?: string,
     *   fulfillment_message?: string,
     *   fulfillment_message_i18n?: array<string, string>,
     *   minimum_age?: int,
     *   products?: array<int, array{
     *     product_id?: string,
     *     description?: string,
     *     description_i18n?: array<string, string>,
     *     quantity?: int,
     *     unit?: string,
     *     price?: string,
     *     image?: string,
     *     taxes?: array<int, array{name: string, tax: string}>,
     *     delivery_date?: array{t_s: int|string}
     *   }>,
     *   timestamp?: array{t_s: int|string},
     *   refund_deadline?: array{t_s: int|string},
     *   pay_deadline?: array{t_s: int|string},
     *   wire_transfer_deadline?: array{t_s: int|string},
     *   merchant_base_url?: string,
     *   delivery_location?: array{
     *     country?: string,
     *     country_subdivision?: string,
     *     district?: string,
     *     town?: string,
     *     town_location?: string,
     *     post_code?: string,
     *     street?: string,
     *     building_name?: string,
     *     building_number?: string,
     *     address_lines?: array<int, string>
     *   },
     *   delivery_date?: array{t_s: int|string},
     *   auto_refund?: array{d_us: int|string},
     *   extra?: object
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        $choices = null;
        if (isset($data['choices'])) {
            $choices = [];
            foreach ($data['choices'] as $choice) {
                /** @var array{amount: string, inputs?: array<int, array{token_family_slug: string, count?: int|null}>, outputs?: array<int, array{type: string, token_family_slug?: string, count?: int|null, valid_at?: array{t_s: int|string}}>, max_fee?: string} $choice */
                $choices[] = OrderChoice::createFromArray($choice);
            }
        }

        $products = null;
        if (isset($data['products'])) {
            $products = array_map(
                static fn (array $product) => Product::fromArray($product),
                $data['products']
            );
        }

        return new self(
            version: $data['version'],
            choices: $choices,
            summary: $data['summary'],
            summary_i18n: $data['summary_i18n'] ?? null,
            order_id: $data['order_id'] ?? null,
            public_reorder_url: $data['public_reorder_url'] ?? null,
            fulfillment_url: $data['fulfillment_url'] ?? null,
            fulfillment_message: $data['fulfillment_message'] ?? null,
            fulfillment_message_i18n: $data['fulfillment_message_i18n'] ?? null,
            minimum_age: $data['minimum_age'] ?? null,
            products: $products,
            timestamp: isset($data['timestamp']) ? Timestamp::fromArray($data['timestamp']) : null,
            refund_deadline: isset($data['refund_deadline']) ? Timestamp::fromArray($data['refund_deadline']) : null,
            pay_deadline: isset($data['pay_deadline']) ? Timestamp::fromArray($data['pay_deadline']) : null,
            wire_transfer_deadline: isset($data['wire_transfer_deadline']) ? Timestamp::fromArray($data['wire_transfer_deadline']) : null,
            merchant_base_url: $data['merchant_base_url'] ?? null,
            delivery_location: isset($data['delivery_location']) ? Location::fromArray($data['delivery_location']) : null,
            delivery_date: isset($data['delivery_date']) ? Timestamp::fromArray($data['delivery_date']) : null,
            auto_refund: isset($data['auto_refund']) ? RelativeTime::fromArray($data['auto_refund']) : null,
            extra: $data['extra'] ?? null
        );
    }
}



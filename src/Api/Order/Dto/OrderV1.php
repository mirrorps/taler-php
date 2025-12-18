<?php

namespace Taler\Api\Order\Dto;

use InvalidArgumentException;
use Taler\Api\Dto\Location;
use Taler\Api\Dto\RelativeTime;
use Taler\Api\Dto\Timestamp;
use Taler\Api\Inventory\Dto\Product;
use Taler\Api\Order\Dto\OrderChoice;

class OrderV1 extends OrderCommon
{
    public readonly int $version;

    /**
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
        public string $summary,
        public ?array $choices = null,
        public ?array $summary_i18n = null,
        public ?string $order_id = null,
        public ?string $public_reorder_url = null,
        public ?string $fulfillment_url = null,
        public ?string $fulfillment_message = null,
        public ?array $fulfillment_message_i18n = null,
        public ?int $minimum_age = null,
        public ?array $products = null,
        public ?Timestamp $timestamp = null,
        public ?Timestamp $refund_deadline = null,
        public ?Timestamp $pay_deadline = null,
        public ?Timestamp $wire_transfer_deadline = null,
        public ?string $merchant_base_url = null,
        public ?Location $delivery_location = null,
        public ?Timestamp $delivery_date = null,
        public ?RelativeTime $auto_refund = null,
        public ?object $extra = null,
        bool $validate = true
    ) {

        parent::__construct(
            summary: $summary,
            summary_i18n: $summary_i18n,
            order_id: $order_id,
            public_reorder_url: $public_reorder_url,
            fulfillment_url: $fulfillment_url,
            fulfillment_message: $fulfillment_message,
            fulfillment_message_i18n: $fulfillment_message_i18n,
            minimum_age: $minimum_age,
            products: $products,
            timestamp: $timestamp,
            refund_deadline: $refund_deadline,
            pay_deadline: $pay_deadline,
            wire_transfer_deadline: $wire_transfer_deadline,
            merchant_base_url: $merchant_base_url,
            delivery_location: $delivery_location,
            delivery_date: $delivery_date,
            auto_refund: $auto_refund,
            extra: $extra,
            validate: false,
        );
        
        $this->version = 1;

        if ($validate) {
            $this->validate();
            parent::validate();
        }
    }

    /**
     * Validates only required variables
     */
    public function validate(): void
    {
        if ($this->choices !== null) {
            foreach ($this->choices as $choice) {
                // @phpstan-ignore-next-line: Runtime guard retained; input may be untyped at runtime
                if (!$choice instanceof OrderChoice) {
                    throw new InvalidArgumentException('Each choice must be an instance of OrderChoice');
                }
                $choice->validate();
            }
        }
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param array{
     *   summary: string,
     *   choices?: array<int, array{
     *     amount: string,
     *     inputs?: array<int, array{token_family_slug: string, count?: int|null}>,
     *     outputs?: array<int, (array{type: 'token', token_family_slug: string, count?: int|null, valid_at?: array{t_s: int|string}}|array{type: 'tax-receipt'})>,
     *     max_fee?: string
     *   }>,
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
     *   extra?: object,
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        // Pre-construction guard to produce consistent exceptions (and avoid TypeErrors)
        // @phpstan-ignore-next-line: Input may be untyped at runtime; keep defensive guard
        if (isset($data['order_id']) && !is_string($data['order_id'])) {
            throw new InvalidArgumentException('Order ID must be a string');
        }

        // Minimal type guards to avoid type errors on constructor
        // @phpstan-ignore-next-line: Input may be untyped at runtime; keep defensive guard
        if (isset($data['summary_i18n']) && !is_array($data['summary_i18n'])) {
            throw new InvalidArgumentException('Summary i18n must be an array of strings');
        }

        if (isset($data['products'])) {
            // @phpstan-ignore-next-line: Input may be untyped at runtime; keep defensive guard
            if (!is_array($data['products'])) {
                throw new InvalidArgumentException('Products must be an array');
            }
            
            foreach ($data['products'] as $product) {
                // @phpstan-ignore-next-line: Elements may be untyped at runtime; keep defensive guard
                if (!is_array($product)) {
                    throw new InvalidArgumentException('Each product must be an array');
                }
            }
        }

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
                static fn (array $product) => Product::createFromArray($product),
                $data['products']
            );
        }

        return new self(
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
            timestamp: isset($data['timestamp']) ? Timestamp::createFromArray($data['timestamp']) : null,
            refund_deadline: isset($data['refund_deadline']) ? Timestamp::createFromArray($data['refund_deadline']) : null,
            pay_deadline: isset($data['pay_deadline']) ? Timestamp::createFromArray($data['pay_deadline']) : null,
            wire_transfer_deadline: isset($data['wire_transfer_deadline']) ? Timestamp::createFromArray($data['wire_transfer_deadline']) : null,
            merchant_base_url: $data['merchant_base_url'] ?? null,
            delivery_location: isset($data['delivery_location']) ? Location::createFromArray($data['delivery_location']) : null,
            delivery_date: isset($data['delivery_date']) ? Timestamp::createFromArray($data['delivery_date']) : null,
            auto_refund: isset($data['auto_refund']) ? RelativeTime::createFromArray($data['auto_refund']) : null,
            extra: $data['extra'] ?? null,
        );
    }
}



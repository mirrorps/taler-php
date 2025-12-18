<?php

namespace Taler\Api\Order\Dto;

use InvalidArgumentException;
use Taler\Api\Dto\Location;
use Taler\Api\Inventory\Dto\Product;
use Taler\Api\Dto\RelativeTime;
use Taler\Api\Dto\Timestamp;

class OrderV0 extends OrderCommon
{
    public readonly int $version;

    /**
     * @param string $summary Human-readable description of the whole purchase
     * @param string $amount Total price for the transaction. The exchange will subtract deposit fees from that amount before transferring it to the merchant.
     * @param string|null $max_fee Maximum total deposit fee accepted by the merchant for this contract. Overrides defaults of the merchant instance.
     * @param array<string, string>|null $summary_i18n Map from IETF BCP 47 language tags to localized summaries
     * @param string|null $order_id Unique identifier for the order
     * @param string|null $public_reorder_url URL where the same contract could be ordered again,
     * @param string|null $fulfillment_url URL for fulfillment
     * @param string|null $fulfillment_message Fulfillment message
     * @param array<string, string>|null $fulfillment_message_i18n Map from IETF BCP 47 language tags to localized fulfillment messages
     * @param int|null $minimum_age Minimum age the buyer must have to buy
     * @param array<Product>|null $products List of products that are part of the purchase
     * @param Timestamp|null $timestamp Time when this contract was generated
     * @param Timestamp|null $refund_deadline After this deadline has passed, no refunds will be accepted
     * @param Timestamp|null $pay_deadline After this deadline, the merchant won't accept payments for the contract
     * @param Timestamp|null $wire_transfer_deadline Transfer deadline for the exchange
     * @param string|null $merchant_base_url Base URL of the (public!) merchant backend API
     * @param Location|null $delivery_location Delivery location for (all!) products
     * @param Timestamp|null $delivery_date Time indicating when the order should be delivered
     * @param RelativeTime|null $auto_refund Specifies for how long the wallet should try to get an automatic refund
     * @param object|null $extra Extra data that is only interpreted by the merchant frontend
     */
    public function __construct(
        public string $summary,
        public string $amount,
        public ?string $max_fee = null,
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
        bool $validate = true,
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

        $this->version = 0;

        if ($validate) {
            $this->validate();
            parent::validate();
        }
    }

    /**
     * @param array<string, mixed> $data
     * @throws InvalidArgumentException
     */
    public static function createFromArray(array $data): self
    {
        // Pre-construction guard to produce consistent exceptions (and avoid TypeErrors)
        if (isset($data['order_id']) && !is_string($data['order_id'])) {
            throw new InvalidArgumentException('Order ID must be a string');
        }

        // Minimal type guards to avoid type errors on constructor
        if (isset($data['summary_i18n']) && !is_array($data['summary_i18n'])) {
            throw new InvalidArgumentException('Summary i18n must be an array of strings');
        }

        if (isset($data['products'])) {
            if (!is_array($data['products'])) {
                throw new InvalidArgumentException('Products must be an array');
            }
            foreach ($data['products'] as $product) {
                if (!is_array($product)) {
                    throw new InvalidArgumentException('Each product must be an array');
                }
            }
        }

        $instance = new self(
            summary: $data['summary'] ?? '',
            amount: $data['amount'] ?? '',
            max_fee: $data['max_fee'] ?? null,
            summary_i18n: $data['summary_i18n'] ?? null,
            order_id: $data['order_id'] ?? null,
            public_reorder_url: $data['public_reorder_url'] ?? null,
            fulfillment_url: $data['fulfillment_url'] ?? null,
            fulfillment_message: $data['fulfillment_message'] ?? null,
            fulfillment_message_i18n: $data['fulfillment_message_i18n'] ?? null,
            minimum_age: $data['minimum_age'] ?? null,
            products: isset($data['products']) ? array_map(
                static fn (array $product) => Product::createFromArray($product),
                $data['products']
            ) : null,
            timestamp: isset($data['timestamp']) ? Timestamp::createFromArray($data['timestamp']) : null,
            refund_deadline: isset($data['refund_deadline']) ? Timestamp::createFromArray($data['refund_deadline']) : null,
            pay_deadline: isset($data['pay_deadline']) ? Timestamp::createFromArray($data['pay_deadline']) : null,
            wire_transfer_deadline: isset($data['wire_transfer_deadline']) ? Timestamp::createFromArray($data['wire_transfer_deadline']) : null,
            merchant_base_url: $data['merchant_base_url'] ?? null,
            delivery_location: isset($data['delivery_location']) ? Location::createFromArray($data['delivery_location']) : null,
            delivery_date: isset($data['delivery_date']) ? Timestamp::createFromArray($data['delivery_date']) : null,
            auto_refund: isset($data['auto_refund']) ? RelativeTime::createFromArray($data['auto_refund']) : null,
            extra: isset($data['extra']) ? $data['extra'] : null,
            validate: isset($data['validate']) ? $data['validate'] : true,
        );

        return $instance;
    }

    /**
     * Validates the DTO data.
     *
     * @throws InvalidArgumentException
     */
    public function validate(): void
    {
        if ($this->amount === '' || trim($this->amount) === '') {
            throw new InvalidArgumentException('Amount is required and must be a non-empty string');
        }
    }
} 
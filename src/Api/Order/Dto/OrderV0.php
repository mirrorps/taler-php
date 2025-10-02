<?php

namespace Taler\Api\Order\Dto;

use InvalidArgumentException;
use Taler\Api\Dto\Location;
use Taler\Api\Inventory\Dto\Product;
use Taler\Api\Dto\RelativeTime;
use Taler\Api\Dto\Timestamp;


/**
 * OrderV0 DTO
 *
 * @phpstan-type OrderV0Array array{
 *   summary: string,
 *   amount: string,
 *   max_fee?: string,
 *   summary_i18n?: array<string, string>,
 *   order_id?: string,
 *   public_reorder_url?: string,
 *   fulfillment_url?: string,
 *   fulfillment_message?: string,
 *   fulfillment_message_i18n?: array<string, string>,
 *   minimum_age?: int,
 *   products?: array<Product>,
 *   timestamp?: Timestamp,
 *   refund_deadline?: Timestamp,
 *   pay_deadline?: Timestamp,
 *   wire_transfer_deadline?: Timestamp,
 *   merchant_base_url?: string,
 *   delivery_location?: Location,
 *   delivery_date?: Timestamp,
 *   auto_refund?: RelativeTime,
 *   extra?: object
 * }
 */
class OrderV0
{
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
     * @param array<string, mixed>|null $special_fields data like $forgettable
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
        public ?array $special_fields = null,
        bool $validate = true,
    ) {
        if (isset($special_fields)) {
            foreach ($special_fields as $key => $value) {
                $this->$key = $value;
            }
            $this->special_fields = null;
        }

        if ($validate) {
            $this->validate();
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
                static fn (array $product) => Product::fromArray($product),
                $data['products']
            ) : null,
            timestamp: isset($data['timestamp']) ? Timestamp::fromArray($data['timestamp']) : null,
            refund_deadline: isset($data['refund_deadline']) ? Timestamp::fromArray($data['refund_deadline']) : null,
            pay_deadline: isset($data['pay_deadline']) ? Timestamp::fromArray($data['pay_deadline']) : null,
            wire_transfer_deadline: isset($data['wire_transfer_deadline']) ? Timestamp::fromArray($data['wire_transfer_deadline']) : null,
            merchant_base_url: $data['merchant_base_url'] ?? null,
            delivery_location: isset($data['delivery_location']) ? Location::fromArray($data['delivery_location']) : null,
            delivery_date: isset($data['delivery_date']) ? Timestamp::fromArray($data['delivery_date']) : null,
            auto_refund: isset($data['auto_refund']) ? RelativeTime::fromArray($data['auto_refund']) : null,
            extra: isset($data['extra']) ? $data['extra'] : null,
            special_fields: isset($data['special_fields']) ? $data['special_fields'] : null,
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
        if ($this->summary === '' || trim($this->summary) === '') {
            throw new InvalidArgumentException('Summary is required and must be a non-empty string');
        }

        if ($this->amount === '' || trim($this->amount) === '') {
            throw new InvalidArgumentException('Amount is required and must be a non-empty string');
        }

        if ($this->summary_i18n !== null) {
            // @phpstan-ignore-next-line: Runtime guard retained for defensive programming
            if (!is_array($this->summary_i18n)) {
                throw new InvalidArgumentException('Summary i18n must be an array of strings');
            }
            foreach ($this->summary_i18n as $k => $v) {
                // @phpstan-ignore-next-line: Keys/values already narrowed by PHPDoc; keep explicit runtime check
                if (!is_string($k) || !is_string($v)) {
                    throw new InvalidArgumentException('Summary i18n must be an array of strings');
                }
            }
        }

        if ($this->order_id !== null) {
            // @phpstan-ignore-next-line: Property typed as string; keep explicit runtime check for robustness
            if (!is_string($this->order_id)) {
                throw new InvalidArgumentException('Order ID must be a string');
            }
            if (!preg_match('/^[A-Za-z0-9.:_-]+$/', $this->order_id)) {
                throw new InvalidArgumentException('Order ID can only contain A-Za-z0-9.:_- characters');
            }
        }

        if ($this->minimum_age !== null) {
            // @phpstan-ignore-next-line: Property is int; retain explicit type guard
            if (!is_int($this->minimum_age) || $this->minimum_age <= 0) {
                throw new InvalidArgumentException('Minimum age must be a positive integer');
            }
        }

        if ($this->products !== null) {
            // @phpstan-ignore-next-line: Property is array; retain explicit structure guard
            if (!is_array($this->products)) {
                throw new InvalidArgumentException('Products must be an array');
            }
            foreach ($this->products as $product) {
                // @phpstan-ignore-next-line: Elements are Product; keep explicit runtime assertion
                if (!$product instanceof Product) {
                    throw new InvalidArgumentException('Each product must be an array');
                }
            }
        }

        if ($this->merchant_base_url !== null) {
            // @phpstan-ignore-next-line: Property typed as string; keep explicit runtime check
            if (!is_string($this->merchant_base_url) || !str_ends_with($this->merchant_base_url, '/')) {
                throw new InvalidArgumentException('Merchant base URL must be an absolute URL that ends with a slash');
            }
            if (!filter_var($this->merchant_base_url, FILTER_VALIDATE_URL)) {
                throw new InvalidArgumentException('Merchant base URL must be a valid URL');
            }
        }
    }
} 
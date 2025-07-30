<?php

namespace Taler\Api\Order\Dto;

use Taler\Api\Dto\RelativeTime;
use Taler\Api\Inventory\Dto\MinimalInventoryProduct;

/**
 * DTO for post order request data.
 */
class PostOrderRequest
{
    /**
     * @param OrderV0|OrderV1 $order The order must at least contain the minimal order detail
     * @param RelativeTime|null $refund_delay If set, the backend will set the refund deadline to the current time plus the specified delay
     * @param string|null $payment_target Specifies the payment target preferred by the client
     * @param string|null $session_id The session for which the payment is made (or replayed)
     * @param array<MinimalInventoryProduct>|null $inventory_products Products to be included from the inventory
     * @param array<string>|null $lock_uuids Lock identifiers used to lock products in the inventory
     * @param bool|null $create_token Should a token for claiming the order be generated
     * @param string|null $otp_id OTP device ID to associate with the order
     * @param bool $validate Whether to validate the data automatically
     */
    public function __construct(
        public readonly OrderV0|OrderV1 $order,
        public readonly ?RelativeTime $refund_delay = null,
        public readonly ?string $payment_target = null,
        public readonly ?string $session_id = null,
        public readonly ?array $inventory_products = null,
        public readonly ?array $lock_uuids = null,
        public readonly ?bool $create_token = null,
        public readonly ?string $otp_id = null,
        bool $validate = true
    ) {
        if ($validate) {
            $this->validate();
        }
    }

    /**
     * Creates a new instance from an array.
     *
     * @param array{
     *     order: array{
     *         version?: int,
     *         summary?: string,
     *         amount?: string,
     *         summary_i18n?: array<string, string>,
     *         order_id?: string,
     *         public_reorder_url?: string,
     *         fulfillment_url?: string,
     *         fulfillment_message?: string,
     *         fulfillment_message_i18n?: array<string, string>,
     *         minimum_age?: int,
     *         products?: array<mixed>,
     *         timestamp?: array<mixed>,
     *         refund_deadline?: array<mixed>,
     *         pay_deadline?: array<mixed>,
     *         wire_transfer_deadline?: array<mixed>,
     *         merchant_base_url?: string,
     *         delivery_location?: array<mixed>,
     *         delivery_date?: array<mixed>,
     *         auto_refund?: array<mixed>,
     *         extra?: object,
     *         choices?: array<array{
     *             amount: string,
     *             inputs?: array<array{token_family_slug: string, count?: int|null}>,
     *             outputs?: array<array{type: string, token_family_slug?: string, count?: int|null, valid_at?: array{t_s: int|string}}>,
     *             max_fee?: string
     *         }>
     *     },
     *     refund_delay?: array{d_us: int|string},
     *     payment_target?: string,
     *     session_id?: string,
     *     inventory_products?: array<array{product_id: string, quantity: int}>,
     *     lock_uuids?: array<string>,
     *     create_token?: bool,
     *     otp_id?: string
     * } $data The data array
     * @return self
     */
    public static function createFromArray(array $data): self
    {
        $orderData = $data['order'];
        $order = isset($orderData['version']) && $orderData['version'] === 1
            ? OrderV1::createFromArray($orderData)
            : OrderV0::createFromArray($orderData);

        return new self(
            order: $order,
            refund_delay: isset($data['refund_delay']) ? RelativeTime::fromArray($data['refund_delay']) : null,
            payment_target: $data['payment_target'] ?? null,
            session_id: $data['session_id'] ?? null,
            inventory_products: isset($data['inventory_products']) 
                ? array_map(
                    fn(array $product) => new MinimalInventoryProduct(
                        product_id: $product['product_id'],
                        quantity: $product['quantity']
                    ),
                    $data['inventory_products']
                )
                : null,
            lock_uuids: $data['lock_uuids'] ?? null,
            create_token: $data['create_token'] ?? null,
            otp_id: $data['otp_id'] ?? null
        );
    }

    /**
     * Validates the DTO data.
     *
     * @throws \InvalidArgumentException If validation fails
     */
    public function validate(): void
    {
        if ($this->inventory_products !== null && empty($this->inventory_products)) {
            throw new \InvalidArgumentException('Inventory products array cannot be empty when provided');
        }

        if ($this->lock_uuids !== null && empty($this->lock_uuids)) {
            throw new \InvalidArgumentException('Lock UUIDs array cannot be empty when provided');
        }
    }
}
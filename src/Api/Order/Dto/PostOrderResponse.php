<?php

namespace Taler\Api\Order\Dto;

/**
 * DTO for post order response data
 */
class PostOrderResponse
{
    /**
     * @param string $order_id Order ID of the response that was just created
     * @param string|null $token Token that authorizes the wallet to claim the order
     * @param bool $validate Whether to validate the data upon construction
     */
    public function __construct(
        public readonly string $order_id,
        public readonly ?string $token = null,
        bool $validate = true
    ) {
        if ($validate) {
            $this->validate();
        }
    }

    /**
     * Validates the DTO data
     * 
     * @throws \InvalidArgumentException If validation fails
     */
    public function validate(): void
    {
        if (empty($this->order_id)) {
            throw new \InvalidArgumentException('Order ID cannot be empty');
        }
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param array{
     *     order_id: string,
     *     token?: string|null
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            order_id: $data['order_id'],
            token: $data['token'] ?? null
        );
    }
}
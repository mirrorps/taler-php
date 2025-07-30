<?php

namespace Taler\Api\Order\Dto;

/**
 * DTO for order input token data
 * 
 * @see https://docs.taler.net/core/api-common.html
 */
class OrderInputToken
{
    private const TYPE = 'token';

    /**
     * @param string $token_family_slug Token family slug as configured in the merchant backend
     * @param int|null $count How many units of the input are required (defaults to 1 if not specified)
     * @param bool $validate Whether to validate the data upon construction
     */
    public function __construct(
        public readonly string $token_family_slug,
        public readonly ?int $count = 1,
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
        if (empty($this->token_family_slug)) {
            throw new \InvalidArgumentException('Token family slug cannot be empty');
        }
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param array{
     *     token_family_slug: string,
     *     count?: int|null
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            token_family_slug: $data['token_family_slug'],
            count: $data['count'] ?? 1
        );
    }

    /**
     * Get the type of the order input
     */
    public function getType(): string
    {
        return self::TYPE;
    }
}
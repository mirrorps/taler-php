<?php

namespace Taler\Api\Order\Dto;

/**
 * OrderV1 DTO
 * 
 * Version 1 order support discounts and subscriptions.
 * @see https://docs.taler.net/design-documents/046-mumimo-contracts.html
 */
class OrderV1
{
    private const VERSION = 1;

    /**
     * @param OrderChoice[]|null $choices List of contract choices that the customer can select from
     * @param bool $validate Whether to validate the data upon construction
     */
    public function __construct(
        public readonly ?array $choices = null,
        bool $validate = true
    ) {
        if ($validate) {
            $this->validate();
        }
    }

    /**
     * Validates the DTO data
     */
    public function validate(): void
    {
        if (isset($this->choices)) {
            foreach ($this->choices as $choice) {
                /** @phpstan-ignore-next-line */
                if (!($choice instanceof OrderChoice)) {
                    throw new \InvalidArgumentException('Each choice must be an instance of OrderChoice');
                }
            }
        }
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param array{
     *     version: int,
     *     choices?: array<array{
     *         amount: string,
     *         inputs?: array<array{token_family_slug: string, count?: int|null}>,
     *         outputs?: array<array{type: string, token_family_slug?: string, count?: int|null, valid_at?: array{t_s: int|string}}>,
     *         max_fee?: string
     *     }>
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        if ($data['version'] !== self::VERSION) {
            throw new \InvalidArgumentException('Version must be 1');
        }

        $choices = null;
        if (isset($data['choices'])) {
            $choices = array_map(
                static fn (array $choice) => OrderChoice::createFromArray($choice),
                $data['choices']
            );
        }

        return new self(
            choices: $choices
        );
    }

    /**
     * Get the version of the order
     */
    public function getVersion(): int
    {
        return self::VERSION;
    }
}
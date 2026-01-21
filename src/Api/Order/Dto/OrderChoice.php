<?php

namespace Taler\Api\Order\Dto;

use function Taler\Helpers\isValidTalerAmount;

/**
 * DTO for order choice data
 */
class OrderChoice
{
    /**
     * @param string $amount Total price for the choice
     * @param OrderInputToken[] $inputs Inputs that must be provided by the customer
     * @param (OrderOutputToken|OrderOutputTaxReceipt)[] $outputs Outputs provided by the merchant
     * @param string|null $max_fee Maximum total deposit fee accepted by the merchant
     * @param bool $validate Whether to validate the data upon construction
     */
    public function __construct(
        public readonly string $amount,
        public readonly array $inputs = [],
        public readonly array $outputs = [],
        public readonly ?string $max_fee = null,
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
        if (empty($this->amount)) {
            throw new \InvalidArgumentException('Amount cannot be empty');
        }

        if (!isValidTalerAmount($this->amount)) {
            throw new \InvalidArgumentException(
                'Amount must be a valid Taler amount in the format CURRENCY:VALUE (e.g., "EUR:1.50")'
            );
        }

        if ($this->max_fee !== null && !isValidTalerAmount($this->max_fee)) {
            throw new \InvalidArgumentException(
                'Max fee must be a valid Taler amount in the format CURRENCY:VALUE (e.g., "EUR:0.10")'
            );
        }

        foreach ($this->inputs as $input) {
            /** @phpstan-ignore-next-line */
            if (!($input instanceof OrderInputToken)) {
                throw new \InvalidArgumentException('Each input must be an instance of OrderInputToken');
            }
        }

        foreach ($this->outputs as $output) {
            /** @phpstan-ignore-next-line */
            if (!($output instanceof OrderOutputToken) && !($output instanceof OrderOutputTaxReceipt)) {
                throw new \InvalidArgumentException('Each output must be an instance of OrderOutputToken or OrderOutputTaxReceipt');
            }
        }
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param array{
     *     amount: string,
     *     inputs?: array<array{token_family_slug: string, count?: int|null}>,
     *     outputs?: array<array{type: string, token_family_slug?: string, count?: int|null, valid_at?: array{t_s: int|string}}>,
     *     max_fee?: string
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        $inputs = [];
        if (isset($data['inputs'])) {
            foreach ($data['inputs'] as $input) {
                $inputs[] = OrderInputToken::createFromArray($input);
            }
        }

        $outputs = [];
        if (isset($data['outputs'])) {
            foreach ($data['outputs'] as $output) {
                if ($output['type'] === 'token' && isset($output['token_family_slug'])) {
                    $outputData = [
                        'token_family_slug' => $output['token_family_slug'],
                        'count' => $output['count'] ?? null,
                        'valid_at' => $output['valid_at'] ?? null
                    ];
                    $outputs[] = OrderOutputToken::createFromArray($outputData);
                } elseif ($output['type'] === 'tax-receipt') {
                    $outputs[] = new OrderOutputTaxReceipt();
                }
            }
        }

        return new self(
            amount: $data['amount'],
            inputs: $inputs,
            outputs: $outputs,
            max_fee: $data['max_fee'] ?? null
        );
    }
}
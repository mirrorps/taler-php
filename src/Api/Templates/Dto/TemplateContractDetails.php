<?php

namespace Taler\Api\Templates\Dto;

use Taler\Api\Dto\RelativeTime;

/**
 * DTO for template contract details.
 */
class TemplateContractDetails implements \JsonSerializable
{
    /**
     * @param string $summary Human-readable description of the purchase
     * @param string $currency Currency code for the amount
     * @param string $amount Total price as Amount string (e.g., "EUR:10.00")
     * @param int $minimum_age Minimum buyer age
     * @param RelativeTime $pay_duration How long the wallet should allow payment
     */
    public function __construct(
        public readonly int $minimum_age,
        public readonly RelativeTime $pay_duration,
        public readonly ?string $summary = null,
        public readonly ?string $currency = null,
        public readonly ?string $amount = null,
    ) {}

    /**
     * @param array{
     *   summary?: string,
     *   currency?: string,
     *   amount?: string,
     *   minimum_age: int,
     *   pay_duration: array{d_us: int|string}
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            minimum_age: $data['minimum_age'],
            pay_duration: RelativeTime::createFromArray($data['pay_duration']),
            summary: $data['summary'] ?? null,
            currency: $data['currency'] ?? null,
            amount: $data['amount'] ?? null,
        );
    }

    /**
     * @return array{
     *   summary: string|null,
     *   currency: string|null,
     *   amount: string|null,
     *   minimum_age: int,
     *   pay_duration: RelativeTime
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'summary' => $this->summary,
            'currency' => $this->currency,
            'amount' => $this->amount,
            'minimum_age' => $this->minimum_age,
            'pay_duration' => $this->pay_duration,
        ];
    }
}



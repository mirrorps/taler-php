<?php
namespace Taler\Api\Order\Dto;

use function Taler\Helpers\isValidTalerAmount;

class Amount implements \JsonSerializable
{
    public readonly string $currency;
    public readonly string $value;
    
    public function __construct(string $amount)
    {
        if (!isValidTalerAmount($amount)) {
            throw new \InvalidArgumentException(
                "Invalid amount format: '$amount'. Expected 'CURRENCY:VALUE' (e.g., 'EUR:10.50')"
            );
        }

        [$this->currency, $this->value] = explode(':', $amount);
    }
    
    public static function createFromCurrencyAndValue(string $currency, string $value): self
    {
        return new self("$currency:$value");
    }
    
    public function jsonSerialize(): string
    {
        return $this->__toString();
    }
    
    public function __toString(): string
    {
        return "{$this->currency}:{$this->value}";
    }
}
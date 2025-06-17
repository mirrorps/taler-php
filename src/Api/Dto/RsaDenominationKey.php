<?php

declare(strict_types=1);

namespace Taler\Api\Dto;

use Taler\Api\Contract\DenominationKey;

/**
 * RSA denomination key implementation.
 */
class RsaDenominationKey implements DenominationKey
{
    private string $cipher = 'RSA';

    public function __construct(
        private int $ageMask, 
        private string $rsaPub
    )
    {
        
    }

    /**
     * @param array{age_mask: int, rsa_pub: string} $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            ageMask: $data['age_mask'],
            rsaPub: $data['rsa_pub']
        );
    }

    public function getCipher(): string
    {
        return $this->cipher;
    }

    public function getAgeMask(): int
    {
        return $this->ageMask;
    }

    public function getRsaPub(): string
    {
        return $this->rsaPub;
    }
} 
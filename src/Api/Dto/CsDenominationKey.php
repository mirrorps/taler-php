<?php

namespace Taler\Api\Dto;

use Taler\Api\Contract\DenominationKey;

/**
 * CS denomination key implementation.
 */
class CsDenominationKey implements DenominationKey
{
    private string $cipher = 'CS';

    public function __construct(
        private int $ageMask, 
        private string $csPub
    )
    {
        
    }

    /**
     * @param array{age_mask: int, cs_pub: string} $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            ageMask: $data['age_mask'],
            csPub: $data['cs_pub']
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

    public function getCsPub(): string
    {
        return $this->csPub;
    }
} 
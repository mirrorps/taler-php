<?php

namespace Taler\Api\Contract;

/**
 * Base interface for denomination keys.
 */
interface DenominationKey
{
    /**
     * Get the cipher type.
     *
     * @return string Either "RSA" or "CS"
     */
    public function getCipher(): string;

    /**
     * Get the age mask.
     *
     * @return int 32-bit age mask
     */
    public function getAgeMask(): int;
} 
<?php

namespace Taler\Api\Dto;

/**
 * DTO for deny-all account restrictions in the exchange wire account details
 *
 * @see https://docs.taler.net/core/api-exchange.html
 */
class DenyAllAccountRestriction
{
    private const TYPE = 'deny';

    /**
     * Returns the type of the restriction
     */
    public function getType(): string
    {
        return self::TYPE;
    }
} 
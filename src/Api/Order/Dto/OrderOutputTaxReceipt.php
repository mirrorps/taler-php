<?php

namespace Taler\Api\Order\Dto;

/**
 * DTO for order output tax receipt data
 * 
 */
class OrderOutputTaxReceipt
{
    private const TYPE = 'tax-receipt';

    public function __construct() { }

    /**
     * Get the type of the order output
     */
    public function getType(): string
    {
        return self::TYPE;
    }
}
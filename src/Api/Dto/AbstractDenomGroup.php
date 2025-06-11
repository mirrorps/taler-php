<?php

namespace Taler\Api\Dto;

use Taler\Api\Contract\DenomGroupCommon;

/**
 * Abstract base class for denomination groups
 */
abstract class AbstractDenomGroup implements DenomGroupCommon
{
    /**
     * @param string $value How much are coins of this denomination worth
     * @param string $fee_withdraw Fee charged by the exchange for withdrawing a coin of this denomination
     * @param string $fee_deposit Fee charged by the exchange for depositing a coin of this denomination
     * @param string $fee_refresh Fee charged by the exchange for refreshing a coin of this denomination
     * @param string $fee_refund Fee charged by the exchange for refunding a coin of this denomination
     * @param array<int, array{
     *     master_sig: string,
     *     stamp_start: array{t_s: int|string},
     *     stamp_expire_withdraw: array{t_s: int|string},
     *     stamp_expire_deposit: array{t_s: int|string},
     *     stamp_expire_legal: array{t_s: int|string},
     *     lost?: bool
     * }> $denoms Array of denomination details
     */
    public function __construct(
        protected readonly string $value,
        protected readonly string $fee_withdraw,
        protected readonly string $fee_deposit,
        protected readonly string $fee_refresh,
        protected readonly string $fee_refund,
        protected readonly array $denoms,
    ) {
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getFeeWithdraw(): string
    {
        return $this->fee_withdraw;
    }

    public function getFeeDeposit(): string
    {
        return $this->fee_deposit;
    }

    public function getFeeRefresh(): string
    {
        return $this->fee_refresh;
    }

    public function getFeeRefund(): string
    {
        return $this->fee_refund;
    }

    /**
     * Get the denomination details
     *
     * @return array<int, array{
     *     master_sig: string,
     *     stamp_start: Timestamp,
     *     stamp_expire_withdraw: Timestamp,
     *     stamp_expire_deposit: Timestamp,
     *     stamp_expire_legal: Timestamp,
     *     lost?: bool
     * }>
     */
    public function getDenoms(): array
    {
        return $this->denoms;
    }

    abstract public function getCipher(): string;
} 
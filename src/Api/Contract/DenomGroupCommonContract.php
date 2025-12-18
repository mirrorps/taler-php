<?php

namespace Taler\Api\Contract;


/**
 * Common attributes for all denomination groups
 * 
 * @see https://docs.taler.net/core/api-common.html
 */
interface DenomGroupCommonContract
{
    /**
     * Get the value of coins of this denomination
     */
    public function getValue(): string;

    /**
     * Get the fee charged by the exchange for withdrawing a coin of this denomination
     */
    public function getFeeWithdraw(): string;

    /**
     * Get the fee charged by the exchange for depositing a coin of this denomination
     */
    public function getFeeDeposit(): string;

    /**
     * Get the fee charged by the exchange for refreshing a coin of this denomination
     */
    public function getFeeRefresh(): string;

    /**
     * Get the fee charged by the exchange for refunding a coin of this denomination
     */
    public function getFeeRefund(): string;

    /**
     * Get the cipher type of this denomination group
     */
    public function getCipher(): string;

    /**
     * Creates a new instance from an array of data
     *
     * @param array{
     *     value: string,
     *     fee_withdraw: string,
     *     fee_deposit: string,
     *     fee_refresh: string,
     *     fee_refund: string,
     *     cipher: string,
     *     denoms: array<int, array{
     *         master_sig: string,
     *         stamp_start: array{t_s: int|string},
     *         stamp_expire_withdraw: array{t_s: int|string},
     *         stamp_expire_deposit: array{t_s: int|string},
     *         stamp_expire_legal: array{t_s: int|string},
     *         lost?: bool
     *     }>,
     *     age_mask?: string
     * } $data
     */
    public static function createFromArray(array $data): self;
} 
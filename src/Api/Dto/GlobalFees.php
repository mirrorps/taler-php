<?php

declare(strict_types=1);

namespace Taler\Api\Dto;

/**
 * DTO for global fees configuration in Taler API responses
 * 
 * @see https://docs.taler.net/core/api-exchange.html
 */
class GlobalFees
{
    /**
     * @param string $start_date What date (inclusive) does these fees go into effect?
     * @param string $end_date What date (exclusive) does this fees stop going into effect?
     * @param string $history_fee Account history fee, charged when a user wants to obtain a reserve/account history.
     * @param string $account_fee Annual fee charged for having an open account at the exchange. Charged to the account.
     *                          If the account balance is insufficient to cover this fee, the account is automatically
     *                          deleted/closed. (Note that the exchange will keep the account history around for longer
     *                          for regulatory reasons.)
     * @param string $purse_fee Purse fee, charged only if a purse is abandoned and was not covered by the account limit.
     * @param RelativeTime $history_expiration How long will the exchange preserve the account history?
     *                                       After an account was deleted/closed, the exchange will retain the account
     *                                       history for legal reasons until this time.
     * @param int $purse_account_limit Non-negative number of concurrent purses that any account holder is allowed to
     *                                create without having to pay the purse_fee.
     * @param RelativeTime $purse_timeout How long does an exchange keep a purse around after a purse has expired
     *                                  (or been successfully merged)? A 'GET' request for a purse will succeed until
     *                                  the purse expiration time plus this value.
     * @param string $master_sig Signature of TALER_GlobalFeesPS.
     */
    public function __construct(
        public readonly string $start_date,
        public readonly string $end_date,
        public readonly string $history_fee,
        public readonly string $account_fee,
        public readonly string $purse_fee,
        public readonly RelativeTime $history_expiration,
        public readonly int $purse_account_limit,
        public readonly RelativeTime $purse_timeout,
        public readonly string $master_sig,
    ) {
        if ($purse_account_limit < 0) {
            throw new \InvalidArgumentException('purse_account_limit must be non-negative');
        }
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param array{
     *     start_date: string,
     *     end_date: string,
     *     history_fee: string,
     *     account_fee: string,
     *     purse_fee: string,
     *     history_expiration: array{d_us: int|string},
     *     purse_account_limit: int,
     *     purse_timeout: array{d_us: int|string},
     *     master_sig: string
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            start_date: $data['start_date'],
            end_date: $data['end_date'],
            history_fee: $data['history_fee'],
            account_fee: $data['account_fee'],
            purse_fee: $data['purse_fee'],
            history_expiration: RelativeTime::fromArray($data['history_expiration']),
            purse_account_limit: $data['purse_account_limit'],
            purse_timeout: RelativeTime::fromArray($data['purse_timeout']),
            master_sig: $data['master_sig']
        );
    }
} 
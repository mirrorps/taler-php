<?php

namespace Taler\Api\Dto;

/**
 * DTO for age-restricted CS-based denomination groups
 */
class DenomGroupCsAgeRestricted extends AbstractDenomGroup
{
    private const CIPHER = 'CS+age_restricted';

    /**
     * @param string $value How much are coins of this denomination worth
     * @param string $fee_withdraw Fee charged by the exchange for withdrawing a coin of this denomination
     * @param string $fee_deposit Fee charged by the exchange for depositing a coin of this denomination
     * @param string $fee_refresh Fee charged by the exchange for refreshing a coin of this denomination
     * @param string $fee_refund Fee charged by the exchange for refunding a coin of this denomination
     * @param array<int, DenomCommon> $denoms Array of denomination details
     * @param string $age_mask Age restriction mask for this denomination group
     */
    public function __construct(
        string $value,
        string $fee_withdraw,
        string $fee_deposit,
        string $fee_refresh,
        string $fee_refund,
        protected readonly array $denoms,
        private readonly string $age_mask,
    ) {
        parent::__construct(
            $value,
            $fee_withdraw,
            $fee_deposit,
            $fee_refresh,
            $fee_refund
        );

    }

    public function getCipher(): string
    {
        return self::CIPHER;
    }

    public function getDenoms(): array
    {
        return $this->denoms;
    }

    public function getAgeMask(): string
    {
        return $this->age_mask;
    }

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
     *         cs_pub: string,
     *         lost?: bool
     *     }>,
     *     age_mask: string
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        if ($data['cipher'] !== self::CIPHER) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid cipher type "%s". Expected "%s"',
                $data['cipher'],
                self::CIPHER
            ));
        }

        return new self(
            value: $data['value'],
            fee_withdraw: $data['fee_withdraw'],
            fee_deposit: $data['fee_deposit'],
            fee_refresh: $data['fee_refresh'],
            fee_refund: $data['fee_refund'],
            denoms: array_map(
                fn(array $denom) => DenomCommon::createFromArray($denom),
                $data['denoms']
            ),
            age_mask: $data['age_mask']
        );
    }
} 
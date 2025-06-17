<?php

namespace Taler\Api\Dto;

/**
 * DTO for future denomination information.
 */
class FutureDenom
{
    public function __construct(
        private string $sectionName,
        private string $value,
        private Timestamp $stampStart,
        private Timestamp $stampExpireWithdraw,
        private Timestamp $stampExpireDeposit,
        private Timestamp $stampExpireLegal,
        private string $denomPub,
        private string $feeWithdraw,
        private string $feeDeposit,
        private string $feeRefresh,
        private string $feeRefund,
        private string $denomSecmodSig
    ) {
    }

    /**
     * Create a FutureDenom instance from an array.
     *
     * @param array<string, mixed> $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            $data['section_name'],
            $data['value'],
            new Timestamp(strtotime($data['stamp_start'])),
            new Timestamp(strtotime($data['stamp_expire_withdraw'])),
            new Timestamp(strtotime($data['stamp_expire_deposit'])),
            new Timestamp(strtotime($data['stamp_expire_legal'])),
            $data['denom_pub'],
            $data['fee_withdraw'],
            $data['fee_deposit'],
            $data['fee_refresh'],
            $data['fee_refund'],
            $data['denom_secmod_sig']
        );
    }

    public function getSectionName(): string
    {
        return $this->sectionName;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getStampStart(): Timestamp
    {
        return $this->stampStart;
    }

    public function getStampExpireWithdraw(): Timestamp
    {
        return $this->stampExpireWithdraw;
    }

    public function getStampExpireDeposit(): Timestamp
    {
        return $this->stampExpireDeposit;
    }

    public function getStampExpireLegal(): Timestamp
    {
        return $this->stampExpireLegal;
    }

    public function getDenomPub(): string
    {
        return $this->denomPub;
    }

    public function getFeeWithdraw(): string
    {
        return $this->feeWithdraw;
    }

    public function getFeeDeposit(): string
    {
        return $this->feeDeposit;
    }

    public function getFeeRefresh(): string
    {
        return $this->feeRefresh;
    }

    public function getFeeRefund(): string
    {
        return $this->feeRefund;
    }

    public function getDenomSecmodSig(): string
    {
        return $this->denomSecmodSig;
    }
} 
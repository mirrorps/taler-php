<?php

namespace Taler\Api\Dto;

class TrackTransferDetail
{
    public function __construct(
        private string $h_contract_terms,
        private string $coin_pub,
        private string $deposit_value,
        private string $deposit_fee,
        private ?string $refund_total
    ) {
    }

    /**
     * @param array<string, string> $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            $data['h_contract_terms'],
            $data['coin_pub'],
            $data['deposit_value'],
            $data['deposit_fee'],
            $data['refund_total'] ?? null
        );
    }

    public function getHContractTerms(): string
    {
        return $this->h_contract_terms;
    }

    public function getCoinPub(): string
    {
        return $this->coin_pub;
    }

    public function getDepositValue(): string
    {
        return $this->deposit_value;
    }

    public function getDepositFee(): string
    {
        return $this->deposit_fee;
    }

    public function getRefundTotal(): ?string
    {
        return $this->refund_total;
    }
} 
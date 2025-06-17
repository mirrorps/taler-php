<?php

namespace Taler\Api\Dto;

class TrackTransferResponse
{
    public function __construct(
        private string $total,
        private string $wire_fee,
        private string $merchant_pub,
        private string $h_payto,
        private Timestamp $execution_time,
        /** @var TrackTransferDetail[] */
        private array $deposits,
        private string $exchange_sig,
        private string $exchange_pub
    ) {
    }

    /**
     * @param array{
     *     total: string,
     *     wire_fee: string,
     *     merchant_pub: string,
     *     h_payto: string,
     *     execution_time: array{t_s: int|string},
     *     deposits: array<array<string, string>>,
     *     exchange_sig: string,
     *     exchange_pub: string
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        $deposits = array_map(
            fn (array $deposit) => TrackTransferDetail::createFromArray($deposit),
            $data['deposits']
        );

        return new self(
            $data['total'],
            $data['wire_fee'],
            $data['merchant_pub'],
            $data['h_payto'],
            Timestamp::fromArray($data['execution_time']),
            $deposits,
            $data['exchange_sig'],
            $data['exchange_pub']
        );
    }

    public function getTotal(): string
    {
        return $this->total;
    }

    public function getWireFee(): string
    {
        return $this->wire_fee;
    }

    public function getMerchantPub(): string
    {
        return $this->merchant_pub;
    }

    public function getHPayto(): string
    {
        return $this->h_payto;
    }

    public function getExecutionTime(): Timestamp
    {
        return $this->execution_time;
    }

    /**
     * @return TrackTransferDetail[]
     */
    public function getDeposits(): array
    {
        return $this->deposits;
    }

    public function getExchangeSig(): string
    {
        return $this->exchange_sig;
    }

    public function getExchangePub(): string
    {
        return $this->exchange_pub;
    }
} 
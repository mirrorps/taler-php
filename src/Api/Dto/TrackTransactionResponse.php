<?php

namespace Taler\Api\Dto;

class TrackTransactionResponse
{
    public function __construct(
        private string $wtid,
        private Timestamp $execution_time,
        private string $coin_contribution,
        private string $exchange_sig,
        private string $exchange_pub
    ) {
    }

    /**
     * @param array{
     *     wtid: string,
     *     execution_time: array{t_s: int|string},
     *     coin_contribution: string,
     *     exchange_sig: string,
     *     exchange_pub: string
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            $data['wtid'],
            Timestamp::fromArray($data['execution_time']),
            $data['coin_contribution'],
            $data['exchange_sig'],
            $data['exchange_pub']
        );
    }

    public function getWtid(): string
    {
        return $this->wtid;
    }

    public function getExecutionTime(): Timestamp
    {
        return $this->execution_time;
    }

    public function getCoinContribution(): string
    {
        return $this->coin_contribution;
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
<?php

namespace Taler\Api\Dto;

/**
 * DTO for future keys response.
 */
class FutureKeysResponse
{
    /**
     * @param FutureDenom[] $futureDenoms
     * @param FutureSignKey[] $futureSignkeys
     */
    public function __construct(
        private array $futureDenoms,
        private array $futureSignkeys,
        private string $masterPub,
        private string $denomSecmodPublicKey,
        private string $signkeySecmodPublicKey
    ) {
    }

    /**
     * Create a FutureKeysResponse instance from an array.
     *
     * @param array<string, mixed> $data
     */
    public static function createFromArray(array $data): self
    {
        $futureDenoms = array_map(
            fn (array $denomData) => FutureDenom::createFromArray($denomData),
            $data['future_denoms']
        );

        $futureSignkeys = array_map(
            fn (array $signkeyData) => FutureSignKey::fromArray([
                'key' => $signkeyData['key'],
                'stamp_start' => ['t_s' => strtotime($signkeyData['stamp_start'])],
                'stamp_expire' => ['t_s' => strtotime($signkeyData['stamp_expire'])],
                'stamp_end' => ['t_s' => strtotime($signkeyData['stamp_expire_legal'])],
                'signkey_secmod_sig' => $signkeyData['key_secmod_sig']
            ]),
            $data['future_signkeys']
        );

        return new self(
            $futureDenoms,
            $futureSignkeys,
            $data['master_pub'],
            $data['denom_secmod_public_key'],
            $data['signkey_secmod_public_key']
        );
    }

    /**
     * @return FutureDenom[]
     */
    public function getFutureDenoms(): array
    {
        return $this->futureDenoms;
    }

    /**
     * @return FutureSignKey[]
     */
    public function getFutureSignkeys(): array
    {
        return $this->futureSignkeys;
    }

    public function getMasterPub(): string
    {
        return $this->masterPub;
    }

    public function getDenomSecmodPublicKey(): string
    {
        return $this->denomSecmodPublicKey;
    }

    public function getSignkeySecmodPublicKey(): string
    {
        return $this->signkeySecmodPublicKey;
    }
} 
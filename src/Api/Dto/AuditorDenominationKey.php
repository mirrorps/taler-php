<?php

namespace Taler\Api\Dto;

class AuditorDenominationKey
{
    /**
     * Constructor for AuditorDenominationKey.
     *
     * @param string $denom_pub_h Hash of the public RSA key. Hash of the public RSA key used to sign coins of the respective denomination.
     *                           Note that the auditor's signature covers more than just the hash, but this
     *                           other information is already provided in denoms and thus not repeated here.
     * @param string $auditor_sig Signature of TALER_ExchangeKeyValidityPS
     */
    public function __construct(
        public readonly string $denom_pub_h, 
        public readonly string $auditor_sig
        )
    {
    }

    /**
     * Create an instance from an array.
     *
     * @param array<string, string> $data
     * @return self
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            $data['denom_pub_h'],
            $data['auditor_sig']
        );
    }
} 
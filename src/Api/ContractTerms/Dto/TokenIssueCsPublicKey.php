<?php

namespace Taler\Api\ContractTerms\Dto;

use Taler\Api\Dto\Timestamp;

/**
 * DTO for CS public key used for token issuance.
 */
class TokenIssueCsPublicKey
{
    private string $cipher = 'CS';

    /**
     * Constructor for TokenIssueCsPublicKey.
     *
     * @param string $cs_pub CS public key (Cs25519Point)
     * @param Timestamp $signature_validity_start Start time of this key's signatures validity period
     * @param Timestamp $signature_validity_end End time of this key's signatures validity period
     */
    public function __construct(
        public readonly string $cs_pub,
        public readonly Timestamp $signature_validity_start,
        public readonly Timestamp $signature_validity_end
    ) {
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param array{
     *     cs_pub: string,
     *     signature_validity_start: array{t_s: int|string},
     *     signature_validity_end: array{t_s: int|string}
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            cs_pub: $data['cs_pub'],
            signature_validity_start: Timestamp::fromArray($data['signature_validity_start']),
            signature_validity_end: Timestamp::fromArray($data['signature_validity_end'])
        );
    }

    public function getCipher(): string
    {
        return $this->cipher;
    }
} 
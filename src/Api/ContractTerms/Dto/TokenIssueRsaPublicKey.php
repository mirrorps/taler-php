<?php

namespace Taler\Api\ContractTerms\Dto;

use Taler\Api\Dto\Timestamp;

/**
 * DTO for RSA public key used for token issuance.
 */
class TokenIssueRsaPublicKey
{
    private string $cipher = 'RSA';

    /**
     * Constructor for TokenIssueRsaPublicKey.
     *
     * @param string $rsa_pub RSA public key
     * @param Timestamp $signature_validity_start Start time of this key's signatures validity period
     * @param Timestamp $signature_validity_end End time of this key's signatures validity period
     */
    public function __construct(
        public readonly string $rsa_pub,
        public readonly Timestamp $signature_validity_start,
        public readonly Timestamp $signature_validity_end
    ) {
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param array{
     *     rsa_pub: string,
     *     signature_validity_start: array{t_s: int|string},
     *     signature_validity_end: array{t_s: int|string}
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            rsa_pub: $data['rsa_pub'],
            signature_validity_start: Timestamp::fromArray($data['signature_validity_start']),
            signature_validity_end: Timestamp::fromArray($data['signature_validity_end'])
        );
    }

    public function getCipher(): string
    {
        return $this->cipher;
    }
} 
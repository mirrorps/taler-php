<?php

namespace Taler\Api\Dto;

/**
 * Represents an auditor's keys and information.
 */
class AuditorKeys
{
    /**
     * Constructor for AuditorKeys.
     *
     * @param string $auditor_pub The auditor's EdDSA signing public key
     * @param string $auditor_url The auditor's URL
     * @param string $auditor_name The auditor's name (for humans)
     * @param array<AuditorDenominationKey> $denomination_keys An array of denomination keys the auditor affirms with its signature
     */
    public function __construct(
        public readonly string $auditor_pub,
        public readonly string $auditor_url,
        public readonly string $auditor_name,
        public readonly array $denomination_keys
    ) {
    }

    /**
     * Create an instance from an array.
     *
     * @param array{
     *     auditor_pub: string,
     *     auditor_url: string,
     *     auditor_name: string,
     *     denomination_keys: array<array{denom_pub_h: string, auditor_sig: string}>
     * } $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $denominationKeys = array_map(
            fn (array $key) => AuditorDenominationKey::fromArray($key),
            $data['denomination_keys']
        );

        return new self(
            $data['auditor_pub'],
            $data['auditor_url'],
            $data['auditor_name'],
            $denominationKeys
        );
    }
} 
<?php

declare(strict_types=1);

namespace Taler\Api\Dto;

/**
 * DTO for denomination revocation under emergency protocol
 * 
 * @see https://docs.taler.net/core/api-exchange.html
 */
class Recoup
{
    /**
     * @param string $h_denom_pub Hash of the public key of the denomination that is being revoked under
     *                           emergency protocol (see /recoup).
     */
    public function __construct(
        public readonly string $h_denom_pub,
    ) {
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param array{h_denom_pub: string} $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            h_denom_pub: $data['h_denom_pub']
        );
    }
} 
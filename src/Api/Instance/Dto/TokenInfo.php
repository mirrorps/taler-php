<?php

namespace Taler\Api\Instance\Dto;

use Taler\Api\Dto\Timestamp;

/**
 * DTO for TokenInfo
 *
 * @since v19
 */
class TokenInfo
{
    /**
     * @param Timestamp $creation_time Time when the token was created
     * @param Timestamp $expiration Time when the token expires
     * @param string $scope Scope for the token
     * @param bool $refreshable Is the token refreshable into a new token during its validity?
     * @param string|null $description Optional token description
     * @param int $serial Opaque unique ID used for pagination
     */
    public function __construct(
        public readonly Timestamp $creation_time,
        public readonly Timestamp $expiration,
        public readonly string $scope,
        public readonly bool $refreshable,
        public readonly ?string $description,
        public readonly int $serial,
    ) {
    }

    /**
     * Create from array
     *
     * @param array{
     *   creation_time: array{t_s: int|string},
     *   expiration: array{t_s: int|string},
     *   scope: string,
     *   refreshable: bool,
     *   description?: string|null,
     *   serial: int
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            creation_time: Timestamp::createFromArray($data['creation_time']),
            expiration: Timestamp::createFromArray($data['expiration']),
            scope: $data['scope'],
            refreshable: $data['refreshable'],
            description: $data['description'] ?? null,
            serial: $data['serial']
        );
    }
}



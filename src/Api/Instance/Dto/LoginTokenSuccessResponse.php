<?php

namespace Taler\Api\Instance\Dto;

use Taler\Api\Dto\Timestamp;

/**
 * DTO for LoginTokenSuccessResponse
 */
class LoginTokenSuccessResponse
{
    /**
     * @param string|null $token Deprecated since v19
     * @param string $access_token The login token for Authorization header (RFC 8959 prefix included)
     * @param "readonly"|"write"|"all"|"order-simple"|"order-pos"|"order-mgmt"|"order-full" $scope Scope of the token
     * @param Timestamp $expiration When the token expires
     * @param bool $refreshable Whether the token can be refreshed
     */
    public function __construct(
        public readonly ?string $token,
        public readonly string $access_token,
        public readonly string $scope,
        public readonly Timestamp $expiration,
        public readonly bool $refreshable,
    ) {
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param array{
     *   token?: string|null,
     *   access_token: string,
     *   scope: "readonly"|"write"|"all"|"order-simple"|"order-pos"|"order-mgmt"|"order-full",
     *   expiration: array{t_s: int|string},
     *   refreshable: bool
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            token: $data['token'] ?? null,
            access_token: $data['access_token'],
            scope: $data['scope'],
            expiration: Timestamp::createFromArray($data['expiration']),
            refreshable: $data['refreshable']
        );
    }
}



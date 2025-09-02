<?php

namespace Taler\Api\Instance\Dto;

use Taler\Api\Dto\RelativeTime;

/**
 * DTO for LoginTokenRequest
 */
class LoginTokenRequest
{
    /**
     * @param "readonly"|"write"|"all"|"order-simple"|"order-pos"|"order-mgmt"|"order-full" $scope Scope of the token
     * @param RelativeTime|null $duration Upper bound on token validity (server may override)
     * @param string|null $description Optional token description
     * @param bool|null $refreshable Deprecated since v19. Use ":refreshable" scope prefix instead.
     * @param bool $validate Whether to validate the data automatically
     */
    public function __construct(
        public readonly string $scope,
        public readonly ?RelativeTime $duration = null,
        public readonly ?string $description = null,
        public readonly ?bool $refreshable = null,
        bool $validate = true
    ) {
        if ($validate) {
            $this->validate();
        }
    }

    /**
     * Validates the DTO data.
     *
     * @throws \InvalidArgumentException If validation fails
     */
    public function validate(): void
    {
        $allowedScopes = [
            'readonly', 'write', 'all', 'order-simple', 'order-pos', 'order-mgmt', 'order-full'
        ];

        if (!in_array($this->scope, $allowedScopes, true)) {
            throw new \InvalidArgumentException('Invalid scope value');
        }
    }

    /**
     * Creates a new instance from an array of data.
     *
     * @param array{
     *   scope: "readonly"|"write"|"all"|"order-simple"|"order-pos"|"order-mgmt"|"order-full",
     *   duration?: array{d_us: int|string}|null,
     *   description?: string|null,
     *   refreshable?: bool|null
     * } $data
     * @return self
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            scope: $data['scope'],
            duration: isset($data['duration']) ? RelativeTime::fromArray($data['duration']) : null,
            description: $data['description'] ?? null,
            refreshable: $data['refreshable'] ?? null
        );
    }
}



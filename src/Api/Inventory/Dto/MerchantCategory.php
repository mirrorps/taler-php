<?php

namespace Taler\Api\Inventory\Dto;

/**
 * Describes a POS category.
 *
 * No validation for response DTOs.
 */
class MerchantCategory
{
    /**
     * @param int $id A unique numeric ID of the category
     * @param string $name Name of the category
     * @param array<string,string>|null $name_i18n Localized names
     */
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?array $name_i18n,
    ) {}

    /**
     * @param array{id: int, name: string, name_i18n?: array<string,string>} $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            name_i18n: $data['name_i18n'] ?? null,
        );
    }
}



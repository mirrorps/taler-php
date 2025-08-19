<?php

namespace Taler\Api\Inventory\Dto;

/**
 * Request DTO for creating a category.
 *
 * Docs shape:
 * {
 *   name: string,
 *   name_i18n?: { [lang_tag: string]: string }
 * }
 */
class CategoryCreateRequest implements \JsonSerializable
{
    /**
     * @param string $name Name of the category
     * @param array<string, string>|null $name_i18n Translations of the name into various languages
     * @param bool $validate Whether to validate the data automatically
     */
    public function __construct(
        public readonly string $name,
        public readonly ?array $name_i18n = null,
        bool $validate = true
    ) {
        if ($validate) {
            $this->validate();
        }
    }

    /**
     * @param array{ name: string, name_i18n?: array<string, string> } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            name_i18n: $data['name_i18n'] ?? null
        );
    }

    /**
     * @throws \InvalidArgumentException If validation fails
     */
    public function validate(): void
    {
        if ($this->name === '' || trim($this->name) === '') {
            throw new \InvalidArgumentException('name must not be empty');
        }

        if ($this->name_i18n !== null) {
            foreach ($this->name_i18n as $lang => $value) {
                if ($lang === '' || trim($lang) === '') {
                    throw new \InvalidArgumentException('name_i18n language tag keys must not be empty');
                }
                if (trim($value) === '') {
                    throw new \InvalidArgumentException('name_i18n values must be non-empty strings');
                }
            }
        }
    }

    /**
     * @return array{ name: string, name_i18n: array<string, string>|null }
     */
    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'name_i18n' => $this->name_i18n,
        ];
    }
}



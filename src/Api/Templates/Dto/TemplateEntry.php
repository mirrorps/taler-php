<?php

namespace Taler\Api\Templates\Dto;

/**
 * DTO for a single template entry in the summary response.
 * Docs: https://docs.taler.net/core/api-merchant.html#get-[-instances-$INSTANCE]-private-templates
 */
class TemplateEntry
{
    /**
     * @param string $template_id
     * @param string $template_description
     */
    public function __construct(
        public readonly string $template_id,
        public readonly string $template_description,
    ) {
    }

    /**
     * @param array{template_id: string, template_description: string} $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            template_id: $data['template_id'],
            template_description: $data['template_description'],
        );
    }
}



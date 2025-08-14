<?php

namespace Taler\Api\Templates\Dto;

/**
 * DTO for the list of templates response (TemplatesSummaryResponse in docs).
 */
class TemplatesSummaryResponse
{
    /**
     * @param array<TemplateEntry> $templates
     */
    public function __construct(
        public readonly array $templates,
    ) {
    }

    /**
     * @param array{templates: array<int, array{template_id: string, template_description: string}>} $data
     */
    public static function createFromArray(array $data): self
    {
        $templates = array_map(
            static fn(array $t) => TemplateEntry::createFromArray($t),
            $data['templates']
        );

        return new self($templates);
    }
}



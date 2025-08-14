<?php

namespace Taler\Api\Templates\Dto;

use Taler\Api\Templates\Dto\TemplateContractDetails;

/**
 * DTO for template details response.
 * Docs: https://docs.taler.net/core/api-merchant.html#get-[-instances-$INSTANCE]-private-templates-$TEMPLATE_ID
 *
 * Note: No validation for response DTOs as requested.
 */
class TemplateDetails
{
    /**
     * @param string $template_description
     * @param TemplateContractDetails $template_contract
     * @param string|null $otp_id
     * @param array<string, mixed>|null $editable_defaults
     * @param string|null $required_currency
     */
    public function __construct(
        public readonly string $template_description,
        public readonly TemplateContractDetails $template_contract,
        public readonly ?string $otp_id = null,
        public readonly ?array $editable_defaults = null,
        public readonly ?string $required_currency = null,
    ) {
    }

    /**
     * @param array{
     *   template_description: string,
     *   template_contract: array{summary?: string, currency?: string, amount?: string, minimum_age: int, pay_duration: array{d_us: int|string}},
     *   otp_id?: string|null,
     *   editable_defaults?: array<string, mixed>|null,
     *   required_currency?: string|null
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            template_description: $data['template_description'],
            template_contract: TemplateContractDetails::createFromArray($data['template_contract']),
            otp_id: $data['otp_id'] ?? null,
            editable_defaults: $data['editable_defaults'] ?? null,
            required_currency: $data['required_currency'] ?? null,
        );
    }
}



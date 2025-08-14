<?php

namespace Taler\Api\Templates\Dto;

/**
 * DTO for updating a Template.
 *
 * Docs: https://docs.taler.net/core/api-merchant.html#patch-[-instances-$INSTANCE]-private-templates-$TEMPLATE_ID
 */
class TemplatePatchDetails implements \JsonSerializable
{
    /**
     * @param string $template_description Human-readable description for the template
     * @param TemplateContractDetails $template_contract Contract defaults contained in the template
     * @param string|null $otp_id Optional OTP device to associate
     * @param array<string, mixed>|null $editable_defaults Optional map of fields that the frontend may pre-fill/edit
     * @param bool $validate Whether to validate inputs
     */
    public function __construct(
        public readonly string $template_description,
        public readonly TemplateContractDetails $template_contract,
        public readonly ?string $otp_id = null,
        public readonly ?array $editable_defaults = null,
        bool $validate = true,
    ) {
        if ($validate) {
            $this->validate();
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function createFromArray(array $data): self
    {
        if (!isset($data['template_description'])) {
            throw new \InvalidArgumentException('template_description is required');
        }
        if (!isset($data['template_contract']) || !is_array($data['template_contract'])) {
            throw new \InvalidArgumentException('template_contract is required');
        }

        /** @var array{summary?: string, currency?: string, amount?: string, minimum_age: int, pay_duration: array{d_us: int|string}} $contractData */
        $contractData = $data['template_contract'];        

        return new self(
            template_description: (string) $data['template_description'],
            template_contract: TemplateContractDetails::createFromArray($contractData),
            otp_id: $data['otp_id'] ?? null,
            editable_defaults: $data['editable_defaults'] ?? null,
        );
    }

    /**
     * Validates the DTO data.
     *
     * @throws \InvalidArgumentException If validation fails
     */
    public function validate(): void
    {
        if (trim($this->template_description) === '') {
            throw new \InvalidArgumentException('template_description must not be empty');
        }

        if ($this->otp_id !== null && trim($this->otp_id) === '') {
            throw new \InvalidArgumentException('otp_id, when provided, must not be empty');
        }
    }

    /**
     * @return array{
     *   template_description: string,
     *   template_contract: TemplateContractDetails,
     *   otp_id: string|null,
     *   editable_defaults: array<string, mixed>|null
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'template_description' => $this->template_description,
            'template_contract' => $this->template_contract,
            'otp_id' => $this->otp_id,
            'editable_defaults' => $this->editable_defaults,
        ];
    }
}




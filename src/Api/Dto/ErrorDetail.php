<?php

namespace Taler\Api\Dto;

/**
 * DTO for error details in API responses
 * 
 * @see https://docs.taler.net/core/api-common.html#tsref-type-ErrorDetail
 */
class ErrorDetail
{
    /**
     * @param int $code Numeric error code unique to the condition
     * @param string|null $hint Human-readable description of the error
     * @param string|null $detail Optional detail about the specific input value that failed
     * @param string|null $parameter Name of the parameter that was bogus (if applicable)
     * @param string|null $path Path to the argument that was bogus (if applicable)
     * @param string|null $offset Offset of the argument that was bogus (if applicable)
     * @param string|null $index Index of the argument that was bogus (if applicable)
     * @param string|null $object Name of the object that was bogus (if applicable)
     * @param string|null $currency Name of the currency that was problematic (if applicable)
     * @param string|null $type_expected Expected type (if applicable)
     * @param string|null $type_actual Type that was provided instead (if applicable)
     * @param array<string, mixed>|null $extra Extra information that doesn't fit into the above (if applicable)
     */
    public function __construct(
        public readonly int $code,
        public readonly ?string $hint = null,
        public readonly ?string $detail = null,
        public readonly ?string $parameter = null,
        public readonly ?string $path = null,
        public readonly ?string $offset = null,
        public readonly ?string $index = null,
        public readonly ?string $object = null,
        public readonly ?string $currency = null,
        public readonly ?string $type_expected = null,
        public readonly ?string $type_actual = null,
        public readonly ?array $extra = null,
    ) {
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param array{
     *     code: int,
     *     hint?: string|null,
     *     detail?: string|null,
     *     parameter?: string|null,
     *     path?: string|null,
     *     offset?: string|null,
     *     index?: string|null,
     *     object?: string|null,
     *     currency?: string|null,
     *     type_expected?: string|null,
     *     type_actual?: string|null,
     *     extra?: array<string, mixed>|null
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            code: $data['code'],
            hint: $data['hint'] ?? null,
            detail: $data['detail'] ?? null,
            parameter: $data['parameter'] ?? null,
            path: $data['path'] ?? null,
            offset: $data['offset'] ?? null,
            index: $data['index'] ?? null,
            object: $data['object'] ?? null,
            currency: $data['currency'] ?? null,
            type_expected: $data['type_expected'] ?? null,
            type_actual: $data['type_actual'] ?? null,
            extra: $data['extra'] ?? null
        );
    }
} 
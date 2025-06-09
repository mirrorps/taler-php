<?php

namespace Taler\Api\Dto;

/**
 * Represents an extension manifest with its criticality, version, and optional configuration.
 */
class ExtensionManifest
{
    /**
     * Constructor for ExtensionManifest.
     *
     * @param bool $critical The criticality of the extension
     * @param string $version The version information in Taler's protocol version ranges notation
     * @param object|null $config Optional configuration object, defined by the feature itself
     */
    public function __construct(
        public readonly bool $critical,
        public readonly string $version,
        public readonly ?object $config = null
    ) {
    }

    /**
     * Create an instance from an array.
     *
     * @param array{
     *     critical: bool,
     *     version: string,
     *     config?: object
     * } $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['critical'],
            $data['version'],
            $data['config'] ?? null
        );
    }
} 
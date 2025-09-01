<?php

namespace Taler\Api\Instance\Dto;

/**
 * DTO for InstanceAuthConfigExternal
 *
 * @deprecated since v20
 */
class InstanceAuthConfigExternal implements \JsonSerializable
{
    const METHOD = 'external';
    
    /**
     * Creates a new instance.
     */
    public function __construct()
    {
    }

    /**
     * Creates a new instance from an array of data.
     *
     * @param array{
     *     method: "external"
     * } $data
     * @return self
     */
    public static function createFromArray(array $data): self
    {
        return new self();
    }

    /**
     * Serializes the object to JSON.
     *
     * @return array{method: string}
     */
    public function jsonSerialize(): array
    {
        return [
            'method' => self::METHOD
        ];
    }
}

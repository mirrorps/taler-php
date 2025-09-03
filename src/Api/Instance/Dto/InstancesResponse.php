<?php

namespace Taler\Api\Instance\Dto;

/**
 * DTO for InstancesResponse
 *
 * Note: Response DTOs do not include validation.
 */
class InstancesResponse
{
    /**
     * @param array<int, Instance> $instances List of instances present in the backend
     */
    public function __construct(
        public readonly array $instances,
    ) {
    }

    /**
     * @param array{instances: array<int, array{
     *   name: string,
     *   id: string,
     *   merchant_pub: string,
     *   payment_targets: array<int, string>,
     *   deleted: bool,
     *   website?: string|null,
     *   logo?: string|null
     * }>} $data
     */
    public static function createFromArray(array $data): self
    {
        $instances = array_map(static fn(array $i) => Instance::createFromArray($i), $data['instances']);
        return new self($instances);
    }
}



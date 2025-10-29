<?php

namespace Taler\Api\DonauCharity\Dto;

/**
 * DTO for DonauInstancesResponse
 *
 * Docs shape:
 * interface DonauInstancesResponse {
 *   donau_instances: DonauInstance[];
 * }
 *
 * No validation for response DTOs.
 */
class DonauInstancesResponse
{
    /**
     * @param array<int, DonauInstance> $donau_instances List of linked charity instances
     */
    public function __construct(
        public readonly array $donau_instances,
    ) {}

    /**
     * @param array{donau_instances: array<int, array{
     *   donau_instance_serial: int,
     *   donau_url: string,
     *   charity_name: string,
     *   charity_pub_key: string,
     *   charity_id: int,
     *   charity_max_per_year: string,
     *   charity_receipts_to_date: string,
     *   current_year: int,
     *   donau_keys_json?: array<string, mixed>
     * }>} $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            donau_instances: array_map(
                static fn(array $i) => DonauInstance::createFromArray($i),
                $data['donau_instances']
            )
        );
    }
}

 


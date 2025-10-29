<?php

namespace Taler\Api\DonauCharity\Dto;

/**
 * DTO for a single Donau charity instance.
 *
 * Docs shape:
 * interface DonauInstance {
 *   donau_instance_serial: Integer;
 *   donau_url: string;
 *   charity_name: string;
 *   charity_pub_key: string; // EdDSA public key (binary) but mapped as string
 *   charity_id: Integer;
 *   charity_max_per_year: string; // Amount
 *   charity_receipts_to_date: string; // Amount
 *   current_year: Integer;
 *   donau_keys_json?: object; // optional
 * }
 *
 * No validation for response DTOs.
 */
class DonauInstance
{
    public function __construct(
        public readonly int $donau_instance_serial,
        public readonly string $donau_url,
        public readonly string $charity_name,
        public readonly string $charity_pub_key,
        public readonly int $charity_id,
        public readonly string $charity_max_per_year,
        public readonly string $charity_receipts_to_date,
        public readonly int $current_year,
        /** @var array<string, mixed>|null */
        public readonly ?array $donau_keys_json = null,
    ) {}

    /**
     * @param array{
     *   donau_instance_serial: int,
     *   donau_url: string,
     *   charity_name: string,
     *   charity_pub_key: string,
     *   charity_id: int,
     *   charity_max_per_year: string,
     *   charity_receipts_to_date: string,
     *   current_year: int,
     *   donau_keys_json?: array<string, mixed>
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            donau_instance_serial: $data['donau_instance_serial'],
            donau_url: $data['donau_url'],
            charity_name: $data['charity_name'],
            charity_pub_key: $data['charity_pub_key'],
            charity_id: $data['charity_id'],
            charity_max_per_year: $data['charity_max_per_year'],
            charity_receipts_to_date: $data['charity_receipts_to_date'],
            current_year: $data['current_year'],
            donau_keys_json: $data['donau_keys_json'] ?? null,
        );
    }
}

 


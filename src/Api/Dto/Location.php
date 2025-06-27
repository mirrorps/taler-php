<?php

namespace Taler\Api\Dto;

/**
 * DTO for Location data
 */
class Location
{
    /**
     * @param string|null $country Nation with its own government
     * @param string|null $country_subdivision Identifies a subdivision of a country such as state, region, county
     * @param string|null $district Identifies a subdivision within a country sub-division
     * @param string|null $town Name of a built-up area, with defined boundaries, and a local government
     * @param string|null $town_location Specific location name within the town
     * @param string|null $post_code Identifier consisting of a group of letters and/or numbers that is added to a postal address
     * @param string|null $street Name of a street or thoroughfare
     * @param string|null $building_name Name of the building or house
     * @param string|null $building_number Number that identifies the position of a building on a street
     * @param array<int, string>|null $address_lines Free-form address lines, should not exceed 7 elements
     */
    public function __construct(
        public readonly ?string $country = null,
        public readonly ?string $country_subdivision = null,
        public readonly ?string $district = null,
        public readonly ?string $town = null,
        public readonly ?string $town_location = null,
        public readonly ?string $post_code = null,
        public readonly ?string $street = null,
        public readonly ?string $building_name = null,
        public readonly ?string $building_number = null,
        public readonly ?array $address_lines = null,
    ) {
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param array{
     *     country?: string|null,
     *     country_subdivision?: string|null,
     *     district?: string|null,
     *     town?: string|null,
     *     town_location?: string|null,
     *     post_code?: string|null,
     *     street?: string|null,
     *     building_name?: string|null,
     *     building_number?: string|null,
     *     address_lines?: array<int, string>|null
     * } $data
     * @return self
     * @throws \InvalidArgumentException When required data is missing or invalid
     */
    public static function fromArray(array $data): self
    {
        return new self(
            country: $data['country'] ?? null,
            country_subdivision: $data['country_subdivision'] ?? null,
            district: $data['district'] ?? null,
            town: $data['town'] ?? null,
            town_location: $data['town_location'] ?? null,
            post_code: $data['post_code'] ?? null,
            street: $data['street'] ?? null,
            building_name: $data['building_name'] ?? null,
            building_number: $data['building_number'] ?? null,
            address_lines: $data['address_lines'] ?? null
        );
    }
} 
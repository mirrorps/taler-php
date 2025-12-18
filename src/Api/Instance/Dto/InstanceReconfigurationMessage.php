<?php

namespace Taler\Api\Instance\Dto;

use Taler\Api\Dto\Location;
use Taler\Api\Dto\RelativeTime;

/**
 * DTO for InstanceReconfigurationMessage
 */
class InstanceReconfigurationMessage
{
    /**
     * @param string $name Merchant name corresponding to this instance
     * @param Location $address Merchant's physical address (to be put into contracts)
     * @param Location $jurisdiction Jurisdiction under which the merchant conducts business
     * @param bool $use_stefan Use STEFAN curves to determine default fees?
     * @param RelativeTime $default_wire_transfer_delay Default wire transfer delay
     * @param RelativeTime $default_pay_delay Default pay delay
     * @param string|null $email Merchant email for customer contact and password reset
     * @param string|null $phone_number Merchant phone number for password reset (2-FA) (@since v21)
     * @param string|null $website Merchant public website
     * @param string|null $logo Merchant logo (ImageDataUrl)
     * @param bool $validate Whether to validate the data automatically
     */
    public function __construct(
        public readonly string $name,
        public readonly Location $address,
        public readonly Location $jurisdiction,
        public readonly bool $use_stefan,
        public readonly RelativeTime $default_wire_transfer_delay,
        public readonly RelativeTime $default_pay_delay,
        public readonly ?string $email = null,
        public readonly ?string $phone_number = null,
        public readonly ?string $website = null,
        public readonly ?string $logo = null,
        bool $validate = true
    ) {
        if ($validate) {
            $this->validate();
        }
    }

    /**
     * Validates the DTO data.
     *
     * @throws \InvalidArgumentException If validation fails
     */
    public function validate(): void
    {
        if ($this->name === '') {
            throw new \InvalidArgumentException('Instance name cannot be empty');
        }

        if ($this->email !== null && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email format');
        }
    }

    /**
     * Creates a new instance from an array of data.
     *
     * @param array{
     *   name: string,
     *   email?: string|null,
     *   phone_number?: string|null,
     *   website?: string|null,
     *   logo?: string|null,
     *   address: array{
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
     *   },
     *   jurisdiction: array{
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
     *   },
     *   use_stefan: bool,
     *   default_wire_transfer_delay: array{d_us: int|string},
     *   default_pay_delay: array{d_us: int|string}
     * } $data
     * @return self
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            address: Location::createFromArray($data['address']),
            jurisdiction: Location::createFromArray($data['jurisdiction']),
            use_stefan: $data['use_stefan'],
            default_wire_transfer_delay: RelativeTime::createFromArray($data['default_wire_transfer_delay']),
            default_pay_delay: RelativeTime::createFromArray($data['default_pay_delay']),
            email: $data['email'] ?? null,
            phone_number: $data['phone_number'] ?? null,
            website: $data['website'] ?? null,
            logo: $data['logo'] ?? null
        );
    }
}



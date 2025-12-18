<?php

namespace Taler\Api\Instance\Dto;

use Taler\Api\Dto\Location;
use Taler\Api\Dto\RelativeTime;

/**
 * DTO for InstanceConfigurationMessage
 */
class InstanceConfigurationMessage
{
    /**
     * @param string $id Name of the merchant instance (matches regex ^[A-Za-z0-9][A-Za-z0-9_.@-]+$)
     * @param string $name Merchant name corresponding to this instance
     * @param string|null $email Merchant email for customer contact and password reset
     * @param string|null $phone_number Merchant phone number for password reset (2-FA) (@since v21)
     * @param string|null $website Merchant public website
     * @param string|null $logo Merchant logo
     * @param InstanceAuthConfigToken|InstanceAuthConfigTokenOLD|InstanceAuthConfigExternal $auth Authentication settings
     * @param Location $address Merchant's physical address (to be put into contracts)
     * @param Location $jurisdiction Jurisdiction under which the merchant conducts business
     * @param bool $use_stefan Use STEFAN curves to determine default fees?
     * @param RelativeTime $default_wire_transfer_delay Default wire transfer delay
     * @param RelativeTime $default_pay_delay Default pay delay
     * @param bool $validate Whether to validate the data automatically
     */
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly InstanceAuthConfigToken|InstanceAuthConfigTokenOLD|InstanceAuthConfigExternal $auth,
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
        if (empty($this->id)) {
            throw new \InvalidArgumentException('Instance ID cannot be empty');
        }

        if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9_.@-]+$/', $this->id)) {
            throw new \InvalidArgumentException('Instance ID must match regex ^[A-Za-z0-9][A-Za-z0-9_.@-]+$');
        }

        if (empty($this->name)) {
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
     *     id: string,
     *     name: string,
     *     email?: string,
     *     phone_number?: string,
     *     website?: string,
     *     logo?: string,
     *     auth: array{
     *         method: string,
     *         password?: string,
     *         token?: string
     *     },
     *     address: array{
     *         country?: string,
     *         country_subdivision?: string,
     *         district?: string,
     *         town?: string,
     *         town_location?: string,
     *         post_code?: string,
     *         street?: string,
     *         building_name?: string,
     *         building_number?: string,
     *         address_lines?: array<int, string>
     *     },
     *     jurisdiction: array{
     *         country?: string,
     *         country_subdivision?: string,
     *         district?: string,
     *         town?: string,
     *         town_location?: string,
     *         post_code?: string,
     *         street?: string,
     *         building_name?: string,
     *         building_number?: string,
     *         address_lines?: array<int, string>
     *     },
     *     use_stefan: bool,
     *     default_wire_transfer_delay: array{d_us: int|string},
     *     default_pay_delay: array{d_us: int|string}
     * } $data
     * @return self
     */
    public static function createFromArray(array $data): self
    {
        /** @var array{method: string, password?: string, token?: string} $authData */
        $authData = $data['auth'];

        $auth = match ($authData['method']) {
            'token' => isset($authData['password'])
                ? InstanceAuthConfigToken::createFromArray($authData)
                : InstanceAuthConfigTokenOLD::createFromArray([
                    'method' => 'token',
                    'token' => $authData['token'] ?? ''
                ]),
            'external' => InstanceAuthConfigExternal::createFromArray($authData),
            default => throw new \InvalidArgumentException('Invalid auth method')
        };

        return new self(
            id: $data['id'],
            name: $data['name'],
            email: $data['email'] ?? null,
            phone_number: $data['phone_number'] ?? null,
            website: $data['website'] ?? null,
            logo: $data['logo'] ?? null,
            auth: $auth,
            address: Location::createFromArray($data['address']),
            jurisdiction: Location::createFromArray($data['jurisdiction']),
            use_stefan: $data['use_stefan'],
            default_wire_transfer_delay: RelativeTime::createFromArray($data['default_wire_transfer_delay']),
            default_pay_delay: RelativeTime::createFromArray($data['default_pay_delay'])
        );
    }
}

<?php

namespace Taler\Api\Instance\Dto;

use Taler\Api\Dto\Location;
use Taler\Api\Dto\RelativeTime;

/**
 * DTO for QueryInstancesResponse
 *
 * Note: Response DTOs do not include validation.
 */
class QueryInstancesResponse
{
    /**
     * @param string $name
     * @param string $merchant_pub EddsaPublicKey
     * @param Location $address
     * @param Location $jurisdiction
     * @param bool $use_stefan
     * @param RelativeTime $default_wire_transfer_delay
     * @param RelativeTime $default_pay_delay
     * @param array{method: string} $auth
     * @param string|null $email
     * @param bool|null $email_validated
     * @param string|null $phone_number
     * @param bool|null $phone_validated
     * @param string|null $website
     * @param string|null $logo ImageDataUrl
     */
    public function __construct(
        public readonly string $name,
        public readonly string $merchant_pub,
        public readonly Location $address,
        public readonly Location $jurisdiction,
        public readonly bool $use_stefan,
        public readonly RelativeTime $default_wire_transfer_delay,
        public readonly RelativeTime $default_pay_delay,
        public readonly array $auth,
        public readonly ?string $email = null,
        public readonly ?bool $email_validated = null,
        public readonly ?string $phone_number = null,
        public readonly ?bool $phone_validated = null,
        public readonly ?string $website = null,
        public readonly ?string $logo = null,
    ) {
    }

    /**
     * @param array{
     *   name: string,
     *   merchant_pub: string,
     *   address: array<string, mixed>,
     *   jurisdiction: array<string, mixed>,
     *   use_stefan: bool,
     *   default_wire_transfer_delay: array{d_us: int|string},
     *   default_pay_delay: array{d_us: int|string},
     *   auth: array{method: string},
     *   email?: string|null,
     *   email_validated?: bool|null,
     *   phone_number?: string|null,
     *   phone_validated?: bool|null,
     *   website?: string|null,
     *   logo?: string|null
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            merchant_pub: $data['merchant_pub'],
            address: Location::createFromArray($data['address']),
            jurisdiction: Location::createFromArray($data['jurisdiction']),
            use_stefan: $data['use_stefan'],
            default_wire_transfer_delay: RelativeTime::createFromArray($data['default_wire_transfer_delay']),
            default_pay_delay: RelativeTime::createFromArray($data['default_pay_delay']),
            auth: $data['auth'],
            email: $data['email'] ?? null,
            email_validated: $data['email_validated'] ?? null,
            phone_number: $data['phone_number'] ?? null,
            phone_validated: $data['phone_validated'] ?? null,
            website: $data['website'] ?? null,
            logo: $data['logo'] ?? null,
        );
    }
}



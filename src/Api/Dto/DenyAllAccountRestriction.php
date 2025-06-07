<?php

namespace Taler\Api\Dto;

use Taler\Api\Contract\AccountRestriction;

/**
 * DTO for deny-all account restrictions in the exchange wire account details
 *
 * @see https://docs.taler.net/core/api-exchange.html
 */
class DenyAllAccountRestriction implements AccountRestriction
{
    private const TYPE = 'deny';

    /**
     * Creates a new instance from an array of data
     *
     * @param array{type?: string} $data
     * @throws \InvalidArgumentException if type is missing or not 'deny'
     */
    public static function fromArray(array $data): self
    {
        if (!isset($data['type'])) {
            throw new \InvalidArgumentException('Missing type field');
        }

        if ($data['type'] !== self::TYPE) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid type for DenyAllAccountRestriction: expected "%s", got "%s"',
                self::TYPE,
                $data['type']
            ));
        }

        return new self();
    }

    /**
     * Returns the type of the restriction
     */
    public function getType(): string
    {
        return self::TYPE;
    }
} 
<?php

namespace Taler\Api\Inventory\Dto;

use Taler\Api\Dto\RelativeTime;

/**
 * Request DTO to lock a certain quantity of a product for a limited duration.
 */
class LockRequest implements \JsonSerializable
{
    /**
     * @param string $lock_uuid UUID that identifies the frontend performing the lock
     * @param RelativeTime $duration How long the frontend intends to hold the lock
     * @param int $quantity How many units should be locked (0 to unlock)
     * @param bool $validate Whether to validate inputs
     */
    public function __construct(
        public readonly string $lock_uuid,
        public readonly RelativeTime $duration,
        public readonly int $quantity,
        bool $validate = true
    ) {
        if ($validate) {
            $this->validate();
        }
    }

    /**
     * @param array{
     *   lock_uuid: string,
     *   duration: array{d_us: int|string},
     *   quantity: int
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            lock_uuid: $data['lock_uuid'],
            duration: RelativeTime::createFromArray($data['duration']),
            quantity: $data['quantity']
        );
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function validate(): void
    {
        if ($this->lock_uuid === '' || trim($this->lock_uuid) === '') {
            throw new \InvalidArgumentException('lock_uuid must not be empty');
        }
        // Basic UUID format check (8-4-4-4-12). Keep permissive to avoid false negatives across UUID versions.
        if (!preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $this->lock_uuid)) {
            throw new \InvalidArgumentException('lock_uuid must be a valid UUID');
        }
        if ($this->quantity < 0) {
            throw new \InvalidArgumentException('quantity must be greater than or equal to zero');
        }
    }

    /**
     * @return array{lock_uuid: string, duration: RelativeTime, quantity: int}
     */
    public function jsonSerialize(): array
    {
        return [
            'lock_uuid' => $this->lock_uuid,
            'duration' => $this->duration,
            'quantity' => $this->quantity,
        ];
    }
}



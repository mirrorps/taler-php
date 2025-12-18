<?php

declare(strict_types=1);

namespace Taler\Api\Dto;

use Taler\Api\Dto\Amount;
use Taler\Api\Dto\RelativeTime;

/**
 * DTO for account limits in Taler API responses
 */
class AccountLimit
{
    /**
     * Valid operation types for account limits
     */
    public const OPERATION_WITHDRAW = 'WITHDRAW';
    public const OPERATION_DEPOSIT = 'DEPOSIT';
    public const OPERATION_MERGE = 'MERGE';
    public const OPERATION_BALANCE = 'BALANCE';
    public const OPERATION_CLOSE = 'CLOSE';
    public const OPERATION_AGGREGATE = 'AGGREGATE';
    public const OPERATION_TRANSACTION = 'TRANSACTION';
    public const OPERATION_REFUND = 'REFUND';

    /**
     * List of all valid operation types
     * @var array<string>
     */
    private const VALID_OPERATIONS = [
        self::OPERATION_WITHDRAW,
        self::OPERATION_DEPOSIT,
        self::OPERATION_MERGE,
        self::OPERATION_BALANCE,
        self::OPERATION_CLOSE,
        self::OPERATION_AGGREGATE,
        self::OPERATION_TRANSACTION,
        self::OPERATION_REFUND
    ];

    /**
     * @param string $operation_type Operation that is limited.
     *                              Must be one of "WITHDRAW", "DEPOSIT",
     *                              (p2p) "MERGE", (wallet) "BALANCE",
     *                              (reserve) "CLOSE", "AGGREGATE",
     *                              "TRANSACTION" or "REFUND".
     * @param RelativeTime $timeframe Timeframe during which the limit applies.
     *                               Not applicable for all operation_types
     *                               (but always present in this object anyway).
     * @param string $threshold Maximum amount allowed during the given timeframe.
     *                         Zero if the operation is simply forbidden.
     * @param bool|null $soft_limit True if this is a soft limit that could be raised
     *                             by passing KYC checks.  Clients *may* deliberately
     *                             try to cross limits and trigger measures resulting
     *                             in 451 responses to begin KYC processes.
     *                             Clients that are aware of hard limits *should*
     *                             inform users about the hard limit and prevent flows
     *                             in the UI that would cause violations of hard limits.
     *                             Made optional in **v21** with a default of 'false' if missing.
     */
    public function __construct(
        public readonly string $operation_type,
        public readonly RelativeTime $timeframe,
        public readonly string $threshold,
        public readonly ?bool $soft_limit = false,
    ) {
        if (!in_array($operation_type, self::VALID_OPERATIONS, true)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid operation type "%s". Must be one of: %s',
                $operation_type,
                implode(', ', self::VALID_OPERATIONS)
            ));
        }
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param array{
     *     operation_type: string,
     *     timeframe: array{d_us: int|string},
     *     threshold: string,
     *     soft_limit?: bool
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            operation_type: $data['operation_type'],
            timeframe: RelativeTime::createFromArray($data['timeframe']),
            threshold: $data['threshold'],
            soft_limit: $data['soft_limit'] ?? false
        );
    }
} 
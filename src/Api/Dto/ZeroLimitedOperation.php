<?php

namespace Taler\Api\Dto;

/**
 * DTO for operations that are limited to zero amount until KYC check
 * 
 * @see https://docs.taler.net/core/api-common.html
 */
class ZeroLimitedOperation
{
    /**
     * Valid operation types for zero-limited operations
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
     * @param string $operation_type Operation that is limited to an amount of zero until the client has passed some KYC check.
     *                              Must be one of "WITHDRAW", "DEPOSIT", "MERGE", "BALANCE", "CLOSE", "AGGREGATE", "TRANSACTION" or "REFUND".
     */
    public function __construct(
        public readonly string $operation_type,
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
     * @param array{operation_type: string} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            operation_type: $data['operation_type']
        );
    }
} 
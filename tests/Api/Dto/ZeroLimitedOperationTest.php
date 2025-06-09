<?php

namespace Tests\Api\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\ZeroLimitedOperation;

class ZeroLimitedOperationTest extends TestCase
{
    /**
     * @dataProvider validOperationTypesProvider
     */
    public function testConstructWithValidOperationType(string $operationType): void
    {
        $operation = new ZeroLimitedOperation($operationType);
        $this->assertSame($operationType, $operation->operation_type);
    }

    /**
     * @dataProvider validOperationTypesProvider
     */
    public function testFromArrayWithValidOperationType(string $operationType): void
    {
        $operation = ZeroLimitedOperation::fromArray(['operation_type' => $operationType]);
        $this->assertSame($operationType, $operation->operation_type);
    }

    public function testConstructWithInvalidOperationType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid operation type "INVALID". Must be one of: WITHDRAW, DEPOSIT, MERGE, BALANCE, CLOSE, AGGREGATE, TRANSACTION, REFUND');
        new ZeroLimitedOperation('INVALID');
    }

    public function testFromArrayWithInvalidOperationType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid operation type "INVALID". Must be one of: WITHDRAW, DEPOSIT, MERGE, BALANCE, CLOSE, AGGREGATE, TRANSACTION, REFUND');
        ZeroLimitedOperation::fromArray(['operation_type' => 'INVALID']);
    }

    public function testObjectImmutability(): void
    {
        $operation = new ZeroLimitedOperation(ZeroLimitedOperation::OPERATION_WITHDRAW);
        $this->assertTrue((new \ReflectionProperty($operation, 'operation_type'))->isReadOnly());
    }

    /**
     * @return array<string, array{string}>
     */
    public function validOperationTypesProvider(): array
    {
        return [
            'WITHDRAW' => [ZeroLimitedOperation::OPERATION_WITHDRAW],
            'DEPOSIT' => [ZeroLimitedOperation::OPERATION_DEPOSIT],
            'MERGE' => [ZeroLimitedOperation::OPERATION_MERGE],
            'BALANCE' => [ZeroLimitedOperation::OPERATION_BALANCE],
            'CLOSE' => [ZeroLimitedOperation::OPERATION_CLOSE],
            'AGGREGATE' => [ZeroLimitedOperation::OPERATION_AGGREGATE],
            'TRANSACTION' => [ZeroLimitedOperation::OPERATION_TRANSACTION],
            'REFUND' => [ZeroLimitedOperation::OPERATION_REFUND],
        ];
    }
} 
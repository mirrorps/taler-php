<?php

namespace Taler\Tests\Api\Order\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Order\Dto\RefundRequest;
use Taler\Exception\TalerException;
use TypeError;

/**
 * Test cases for RefundRequest DTO.
 */
final class RefundRequestTest extends TestCase
{
    /**
     * Test successful creation of RefundRequest.
     */
    public function testCreateSuccess(): void
    {
        $refundRequest = new RefundRequest(
            refund: '10.00',
            reason: 'Customer dissatisfaction'
        );

        $this->assertSame('10.00', $refundRequest->refund);
        $this->assertSame('Customer dissatisfaction', $refundRequest->reason);
    }

    /**
     * Test successful creation from array.
     */
    public function testCreateFromArraySuccess(): void
    {
        $data = [
            'refund' => '10.00',
            'reason' => 'Customer dissatisfaction'
        ];

        $refundRequest = RefundRequest::createFromArray($data);

        $this->assertSame('10.00', $refundRequest->refund);
        $this->assertSame('Customer dissatisfaction', $refundRequest->reason);
    }

    /**
     * Test validation failure for empty refund.
     */
    public function testValidationFailureEmptyRefund(): void
    {
        $this->expectException(TalerException::class);
        $this->expectExceptionMessage('Refund amount is required');

        new RefundRequest(
            refund: '',
            reason: 'Customer dissatisfaction'
        );
    }

    /**
     * Test validation failure for empty reason.
     */
    public function testValidationFailureEmptyReason(): void
    {
        $this->expectException(TalerException::class);
        $this->expectExceptionMessage('Refund reason is required');

        new RefundRequest(
            refund: '10.00',
            reason: ''
        );
    }

    /**
     * Test creation without validation.
     */
    public function testCreateWithoutValidation(): void
    {
        $refundRequest = new RefundRequest(
            refund: '',
            reason: '',
            validate: false
        );

        $this->assertSame('', $refundRequest->refund);
        $this->assertSame('', $refundRequest->reason);
    }
} 
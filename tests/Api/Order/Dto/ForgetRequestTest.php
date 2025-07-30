<?php

namespace Taler\Tests\Api\Order\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Order\Dto\ForgetRequest;

/**
 * Test cases for ForgetRequest DTO.
 */
class ForgetRequestTest extends TestCase
{
    /**
     * Test valid creation from constructor.
     */
    public function testValidConstruction(): void
    {
        $fields = ['$.wire_fee', '$.products[0].description'];
        $request = new ForgetRequest($fields);
        
        $this->assertSame($fields, $request->fields);
    }

    /**
     * Test valid creation from array.
     */
    public function testCreateFromArray(): void
    {
        $data = [
            'fields' => ['$.wire_fee', '$.products[0].description']
        ];
        
        $request = ForgetRequest::createFromArray($data);
        
        $this->assertSame($data['fields'], $request->fields);
    }

    /**
     * Test validation with empty fields array.
     */
    public function testValidationEmptyFields(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Fields array cannot be empty');
        
        new ForgetRequest([]);
    }

    /**
     * Test validation with invalid field format.
     */
    public function testValidationInvalidFieldFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Field must start with $.');
        
        new ForgetRequest(['invalid_path']);
    }

    /**
     * Test validation with field ending in array index.
     */
    public function testValidationFieldEndsWithArrayIndex(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Field cannot end with an array index or wildcard');
        
        new ForgetRequest(['$.products[0]']);
    }

    /**
     * Test validation with field ending in wildcard.
     */
    public function testValidationFieldEndsWithWildcard(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Field cannot end with an array index or wildcard');
        
        new ForgetRequest(['$.products[*]']);
    }

    /**
     * Test skipping validation.
     */
    public function testSkipValidation(): void
    {
        $fields = ['invalid_path'];  // Would normally fail validation
        $request = new ForgetRequest($fields, false);
        
        $this->assertSame($fields, $request->fields);
    }
}
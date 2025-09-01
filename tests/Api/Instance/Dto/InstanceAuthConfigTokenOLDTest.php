<?php

namespace Taler\Tests\Api\Instance\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Instance\Dto\InstanceAuthConfigTokenOLD;

/**
 * Test cases for InstanceAuthConfigTokenOLD DTO.
 */
class InstanceAuthConfigTokenOLDTest extends TestCase
{
    /**
     * Test valid construction.
     */
    public function testValidConstruction(): void
    {
        $token = new InstanceAuthConfigTokenOLD('secret-token:test-token');

        $this->assertSame('secret-token:test-token', $token->token);
    }

    /**
     * Test validation with empty token.
     */
    public function testValidationEmptyToken(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Token cannot be empty');

        new InstanceAuthConfigTokenOLD('');
    }

    /**
     * Test validation with invalid token prefix.
     */
    public function testValidationInvalidTokenPrefix(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Token must begin with "secret-token:"');

        new InstanceAuthConfigTokenOLD('invalid-token');
    }

    /**
     * Test skipping validation.
     */
    public function testSkipValidation(): void
    {
        $token = new InstanceAuthConfigTokenOLD('invalid-token', false);

        $this->assertSame('invalid-token', $token->token);
    }

    /**
     * Test createFromArray.
     */
    public function testCreateFromArray(): void
    {
        $data = [
            'method' => 'token',
            'token' => 'secret-token:test-token'
        ];

        $token = InstanceAuthConfigTokenOLD::createFromArray($data);

        $this->assertInstanceOf(InstanceAuthConfigTokenOLD::class, $token);
        $this->assertSame('secret-token:test-token', $token->token);
    }
}

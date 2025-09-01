<?php

namespace Taler\Tests\Api\Instance\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Instance\Dto\InstanceAuthConfigToken;

/**
 * Test cases for InstanceAuthConfigToken DTO.
 */
class InstanceAuthConfigTokenTest extends TestCase
{
    /**
     * Test valid construction.
     */
    public function testValidConstruction(): void
    {
        $token = new InstanceAuthConfigToken('test-password');

        $this->assertSame('test-password', $token->password);
    }

    /**
     * Test validation with empty password.
     */
    public function testValidationEmptyPassword(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Password cannot be empty');

        new InstanceAuthConfigToken('');
    }

    /**
     * Test skipping validation.
     */
    public function testSkipValidation(): void
    {
        $token = new InstanceAuthConfigToken('', false);

        $this->assertSame('', $token->password);
    }

    /**
     * Test createFromArray.
     */
    public function testCreateFromArray(): void
    {
        $data = [
            'method' => 'token',
            'password' => 'test-password'
        ];

        $token = InstanceAuthConfigToken::createFromArray($data);

        $this->assertInstanceOf(InstanceAuthConfigToken::class, $token);
        $this->assertSame('test-password', $token->password);
    }
}

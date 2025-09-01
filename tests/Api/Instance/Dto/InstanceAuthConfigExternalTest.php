<?php

namespace Taler\Tests\Api\Instance\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Instance\Dto\InstanceAuthConfigExternal;

/**
 * Test cases for InstanceAuthConfigExternal DTO.
 */
class InstanceAuthConfigExternalTest extends TestCase
{
    /**
     * Test valid construction.
     */
    public function testValidConstruction(): void
    {
        $external = new InstanceAuthConfigExternal();

        $this->assertInstanceOf(InstanceAuthConfigExternal::class, $external);
    }

    /**
     * Test createFromArray.
     */
    public function testCreateFromArray(): void
    {
        $data = [
            'method' => 'external'
        ];

        $external = InstanceAuthConfigExternal::createFromArray($data);

        $this->assertInstanceOf(InstanceAuthConfigExternal::class, $external);
    }
}

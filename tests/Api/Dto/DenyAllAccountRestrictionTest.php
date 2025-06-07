<?php

namespace Taler\Tests\Api\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\DenyAllAccountRestriction;

class DenyAllAccountRestrictionTest extends TestCase
{
    public function testConstruct(): void
    {
        $restriction = new DenyAllAccountRestriction();

        $this->assertSame('deny', $restriction->getType());
    }
}
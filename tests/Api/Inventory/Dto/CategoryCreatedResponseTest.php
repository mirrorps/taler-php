<?php

namespace Taler\Tests\Api\Inventory\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Inventory\Dto\CategoryCreatedResponse;

class CategoryCreatedResponseTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $res = CategoryCreatedResponse::createFromArray(['category_id' => 123]);
        $this->assertSame(123, $res->category_id);
    }
}



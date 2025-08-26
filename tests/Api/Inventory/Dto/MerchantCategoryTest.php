<?php

namespace Taler\Tests\Api\Inventory\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Inventory\Dto\MerchantCategory;

class MerchantCategoryTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = ['id' => 1, 'name' => 'Drinks', 'name_i18n' => ['en' => 'Drinks']];
        $cat = MerchantCategory::createFromArray($data);
        $this->assertSame(1, $cat->id);
        $this->assertSame('Drinks', $cat->name);
        $this->assertSame('Drinks', $cat->name_i18n['en']);
    }
}



<?php

namespace Taler\Tests\Api\Inventory\Dto;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Taler\Api\Inventory\Dto\CategoryCreateRequest;

class CategoryCreateRequestTest extends TestCase
{
    public function testValidConstruction(): void
    {
        $req = new CategoryCreateRequest('Beverages', ['en' => 'Beverages']);
        $this->assertSame('Beverages', $req->name);
        $this->assertSame('Beverages', $req->name_i18n['en']);
        $this->assertSame(['name' => 'Beverages', 'name_i18n' => ['en' => 'Beverages']], $req->jsonSerialize());
    }

    public function testCreateFromArray(): void
    {
        $req = CategoryCreateRequest::createFromArray(['name' => 'Snacks']);
        $this->assertSame('Snacks', $req->name);
        $this->assertNull($req->name_i18n);
    }

    public function testValidationEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CategoryCreateRequest('   ');
    }

    public function testValidationInvalidI18n(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CategoryCreateRequest('Name', ['' => 'x']);
    }
}



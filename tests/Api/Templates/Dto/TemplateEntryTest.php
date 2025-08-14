<?php

namespace Taler\Tests\Api\Templates\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Templates\Dto\TemplateEntry;

class TemplateEntryTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = [
            'template_id' => 'invoice-basic',
            'template_description' => 'Basic invoice template',
        ];

        $dto = TemplateEntry::createFromArray($data);

        $this->assertSame('invoice-basic', $dto->template_id);
        $this->assertSame('Basic invoice template', $dto->template_description);
    }
}



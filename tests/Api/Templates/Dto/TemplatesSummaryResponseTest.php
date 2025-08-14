<?php

namespace Taler\Tests\Api\Templates\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Templates\Dto\TemplateEntry;
use Taler\Api\Templates\Dto\TemplatesSummaryResponse;

class TemplatesSummaryResponseTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = [
            'templates' => [
                ['template_id' => 't1', 'template_description' => 'First'],
                ['template_id' => 't2', 'template_description' => 'Second'],
            ],
        ];

        $dto = TemplatesSummaryResponse::createFromArray($data);

        $this->assertCount(2, $dto->templates);
        $this->assertInstanceOf(TemplateEntry::class, $dto->templates[0]);
        $this->assertSame('t1', $dto->templates[0]->template_id);
        $this->assertSame('Second', $dto->templates[1]->template_description);
    }
}



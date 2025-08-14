<?php

namespace Taler\Tests\Api\Templates\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\RelativeTime;
use Taler\Api\Templates\Dto\TemplateAddDetails;
use Taler\Api\Templates\Dto\TemplateContractDetails;

class TemplateAddDetailsTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = [
            'template_id' => 'tpl-1',
            'template_description' => 'Invoice template',
            'template_contract' => [
                'summary' => 'Service fee',
                'currency' => 'EUR',
                'amount' => 'EUR:10.00',
                'minimum_age' => 18,
                'pay_duration' => ['d_us' => 3600000000]
            ],
            'otp_id' => 'otp-1',
            'editable_defaults' => [
                'summary' => 'Editable'
            ]
        ];

        $dto = TemplateAddDetails::createFromArray($data);

        $this->assertSame('tpl-1', $dto->template_id);
        $this->assertSame('Invoice template', $dto->template_description);
        $this->assertSame('otp-1', $dto->otp_id);
        $this->assertIsArray($dto->editable_defaults);
        $this->assertInstanceOf(TemplateContractDetails::class, $dto->template_contract);
        $this->assertSame('Service fee', $dto->template_contract->summary);
        $this->assertSame('EUR', $dto->template_contract->currency);
        $this->assertSame('EUR:10.00', $dto->template_contract->amount);
        $this->assertSame(18, $dto->template_contract->minimum_age);
        $this->assertInstanceOf(RelativeTime::class, $dto->template_contract->pay_duration);
        $this->assertSame(3600000000, $dto->template_contract->pay_duration->d_us);
    }
}



<?php

namespace Taler\Tests\Api\Templates\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\RelativeTime;
use Taler\Api\Templates\Dto\TemplateContractDetails;
use Taler\Api\Templates\Dto\TemplatePatchDetails;

class TemplatePatchDetailsTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = [
            'template_description' => 'Invoice template updated',
            'template_contract' => [
                'summary' => 'Service fee',
                'currency' => 'EUR',
                'amount' => 'EUR:20.00',
                'minimum_age' => 21,
                'pay_duration' => ['d_us' => 7200000000]
            ],
            'otp_id' => 'otp-2',
            'editable_defaults' => [
                'summary' => 'Editable updated'
            ]
        ];

        $dto = TemplatePatchDetails::createFromArray($data);

        $this->assertSame('Invoice template updated', $dto->template_description);
        $this->assertInstanceOf(TemplateContractDetails::class, $dto->template_contract);
        $this->assertSame('Service fee', $dto->template_contract->summary);
        $this->assertSame('EUR', $dto->template_contract->currency);
        $this->assertSame('EUR:20.00', $dto->template_contract->amount);
        $this->assertSame(21, $dto->template_contract->minimum_age);
        $this->assertInstanceOf(RelativeTime::class, $dto->template_contract->pay_duration);
        $this->assertSame(7200000000, $dto->template_contract->pay_duration->d_us);
        $this->assertSame('otp-2', $dto->otp_id);
        $this->assertIsArray($dto->editable_defaults);
    }

    public function testValidationRejectsWhitespaceDescription(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new TemplatePatchDetails(
            template_description: '   ',
            template_contract: new TemplateContractDetails(
                minimum_age: 18,
                pay_duration: new RelativeTime(3600)
            )
        );
    }

    public function testValidationEmptyStrings(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new TemplatePatchDetails(
            template_description: '',
            template_contract: new TemplateContractDetails(
                minimum_age: 18,
                pay_duration: new RelativeTime(3600)
            ),
            otp_id: null,
            editable_defaults: null
        );
    }

    public function testValidationEmptyOtpId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new TemplatePatchDetails(
            template_description: 'desc',
            template_contract: new TemplateContractDetails(
                minimum_age: 18,
                pay_duration: new RelativeTime(3600)
            ),
            otp_id: '',
            editable_defaults: null
        );
    }
}




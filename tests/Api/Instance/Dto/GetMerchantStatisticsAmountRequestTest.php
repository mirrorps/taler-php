<?php

namespace Taler\Tests\Api\Instance\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Instance\Dto\GetMerchantStatisticsAmountRequest;

class GetMerchantStatisticsAmountRequestTest extends TestCase
{
    public function testToArrayEmpty(): void
    {
        $dto = new GetMerchantStatisticsAmountRequest();
        $this->assertSame([], $dto->toArray());
    }

    public function testToArrayFull(): void
    {
        $dto = new GetMerchantStatisticsAmountRequest(by: 'BUCKET');
        $this->assertSame(['by' => 'BUCKET'], $dto->toArray());
    }

    public function testValidationInvalidBy(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new GetMerchantStatisticsAmountRequest(by: 'INVALID');
    }
}



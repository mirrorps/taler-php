<?php

namespace Taler\Tests\Api\Instance\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Instance\Dto\GetMerchantStatisticsCounterRequest;

class GetMerchantStatisticsCounterRequestTest extends TestCase
{
    public function testToArrayEmpty(): void
    {
        $dto = new GetMerchantStatisticsCounterRequest();
        $this->assertSame([], $dto->toArray());
    }

    public function testToArrayFull(): void
    {
        $dto = new GetMerchantStatisticsCounterRequest(by: 'BUCKET');
        $this->assertSame(['by' => 'BUCKET'], $dto->toArray());
    }

    public function testValidationInvalidBy(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new GetMerchantStatisticsCounterRequest(by: 'INVALID');
    }
}




<?php

namespace Taler\Tests\Api\Instance\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Instance\Dto\MerchantStatisticsAmountResponse;
use Taler\Api\Instance\Dto\MerchantStatisticAmountByBucket;
use Taler\Api\Instance\Dto\MerchantStatisticAmountByInterval;

class MerchantStatisticsAmountResponseTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = [
            'buckets' => [
                [
                    'start_time' => ['t_s' => 100],
                    'end_time' => ['t_s' => 200],
                    'range' => 'day',
                    'cumulative_amounts' => ['EUR:1.23', 'EUR:3.00']
                ]
            ],
            'intervals' => [
                [
                    'start_time' => ['t_s' => 10],
                    'cumulative_amounts' => ['EUR:5.00']
                ]
            ],
            'buckets_description' => 'Per day',
            'intervals_description' => 'Last hours'
        ];

        $resp = MerchantStatisticsAmountResponse::createFromArray($data);
        $this->assertInstanceOf(MerchantStatisticsAmountResponse::class, $resp);
        $this->assertCount(1, $resp->buckets);
        $this->assertCount(1, $resp->intervals);
        $this->assertInstanceOf(MerchantStatisticAmountByBucket::class, $resp->buckets[0]);
        $this->assertInstanceOf(MerchantStatisticAmountByInterval::class, $resp->intervals[0]);
        $this->assertSame('Per day', $resp->buckets_description);
        $this->assertSame('Last hours', $resp->intervals_description);
        $this->assertSame(['EUR:1.23', 'EUR:3.00'], $resp->buckets[0]->cumulative_amounts);
        $this->assertSame(['EUR:5.00'], $resp->intervals[0]->cumulative_amounts);
    }
}



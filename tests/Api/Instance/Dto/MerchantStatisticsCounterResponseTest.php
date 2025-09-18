<?php

namespace Taler\Tests\Api\Instance\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Instance\Dto\MerchantStatisticsCounterResponse;
use Taler\Api\Instance\Dto\MerchantStatisticCounterByBucket;
use Taler\Api\Instance\Dto\MerchantStatisticCounterByInterval;

class MerchantStatisticsCounterResponseTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = [
            'buckets' => [
                [
                    'start_time' => ['t_s' => 100],
                    'end_time' => ['t_s' => 200],
                    'range' => 'day',
                    'cumulative_counter' => 42
                ]
            ],
            'intervals' => [
                [
                    'start_time' => ['t_s' => 10],
                    'cumulative_counter' => 7
                ]
            ],
            'buckets_description' => 'Per day',
            'intervals_description' => 'Last hours'
        ];

        $resp = MerchantStatisticsCounterResponse::createFromArray($data);
        $this->assertInstanceOf(MerchantStatisticsCounterResponse::class, $resp);
        $this->assertCount(1, $resp->buckets);
        $this->assertCount(1, $resp->intervals);
        $this->assertInstanceOf(MerchantStatisticCounterByBucket::class, $resp->buckets[0]);
        $this->assertInstanceOf(MerchantStatisticCounterByInterval::class, $resp->intervals[0]);
        $this->assertSame('Per day', $resp->buckets_description);
        $this->assertSame('Last hours', $resp->intervals_description);
        $this->assertSame(42, $resp->buckets[0]->cumulative_counter);
        $this->assertSame(7, $resp->intervals[0]->cumulative_counter);
    }
}




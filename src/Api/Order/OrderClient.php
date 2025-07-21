<?php

namespace Taler\Api\Order;

use Taler\Api\Base\AbstractApiClient;
use Taler\Api\Order\Dto\CheckPaymentClaimedResponse;
use Taler\Api\Order\Dto\CheckPaymentPaidResponse;
use Taler\Api\Order\Dto\CheckPaymentUnpaidResponse;
use Taler\Api\Order\Dto\OrderHistory;
use Taler\Exception\TalerException;

class OrderClient extends AbstractApiClient
{
    /**
     * @param array<string, string> $params HTTP params
     * @param array<string, string> $headers Optional request headers
     * @return OrderHistory|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public function getOrders(array $params = [], array $headers = []): OrderHistory|array
    {
        return Actions\GetOrders::run($this, $params, $headers);
    }

    /**
     * @param array<string, string> $params HTTP params
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function getOrdersAsync(array $params = [], array $headers = []): mixed
    {
        return Actions\GetOrders::runAsync($this, $params, $headers);
    }

    /**
     * @param array<string, string> $params HTTP params
     * @param array<string, string> $headers Optional request headers
     * @return CheckPaymentPaidResponse|CheckPaymentClaimedResponse|CheckPaymentUnpaidResponse|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public function getOrder(string $orderId, array $params = [], array $headers = []): CheckPaymentPaidResponse|CheckPaymentClaimedResponse|CheckPaymentUnpaidResponse|array
    {
        return Actions\GetOrder::run($this, $orderId, $params, $headers);
    }

    // public function getOrderAsync(string $orderId, array $params = [], array $headers = []): mixed
    // {
    //     return Actions\GetOrder::runAsync($this, $orderId, $params, $headers);
    // }
}
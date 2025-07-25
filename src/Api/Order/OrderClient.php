<?php

namespace Taler\Api\Order;

use Taler\Api\Base\AbstractApiClient;
use Taler\Api\Order\Dto\CheckPaymentClaimedResponse;
use Taler\Api\Order\Dto\CheckPaymentPaidResponse;
use Taler\Api\Order\Dto\CheckPaymentUnpaidResponse;
use Taler\Api\Order\Dto\MerchantRefundResponse;
use Taler\Api\Order\Dto\OrderHistory;
use Taler\Api\Order\Dto\RefundRequest;
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
     * @param string $orderId
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

    /**
     * @param string $orderId
     * @param array<string, string> $params HTTP params
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function getOrderAsync(string $orderId, array $params = [], array $headers = []): mixed
    {
        return Actions\GetOrder::runAsync($this, $orderId, $params, $headers);
    }

    /**
     * Initiates a refund for a specific order.
     *
     * @param string $orderId The order ID to refund
     * @param RefundRequest $refundRequest The refund request data
     * @param array<string, string> $headers Optional request headers
     * @return MerchantRefundResponse|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public function refundOrder(string $orderId, RefundRequest $refundRequest, array $headers = []): MerchantRefundResponse|array
    {
        return Actions\RefundOrder::run($this, $orderId, $refundRequest, $headers);
    }

    /**
     * Initiates a refund for a specific order asynchronously.
     *
     * @param string $orderId The order ID to refund
     * @param RefundRequest $refundRequest The refund request data
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function refundOrderAsync(string $orderId, RefundRequest $refundRequest, array $headers = []): mixed
    {
        return Actions\RefundOrder::runAsync($this, $orderId, $refundRequest, $headers);
    }
}
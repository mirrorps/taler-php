<?php

namespace Taler\Api\Order;

use Taler\Api\Base\AbstractApiClient;
use Taler\Api\Order\Dto\CheckPaymentClaimedResponse;
use Taler\Api\Order\Dto\CheckPaymentPaidResponse;
use Taler\Api\Order\Dto\CheckPaymentUnpaidResponse;
use Taler\Api\Order\Dto\MerchantRefundResponse;
use Taler\Api\Order\Dto\OrderHistory;
use Taler\Api\Order\Dto\PostOrderRequest;
use Taler\Api\Order\Dto\PostOrderResponse;
use Taler\Api\Order\Dto\RefundRequest;
use Taler\Api\Order\Dto\ForgetRequest;
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
     * @param PostOrderRequest $postOrderRequest
     * @param array<string, string> $headers Optional request headers
     * @return PostOrderResponse|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public function createOrder(PostOrderRequest $postOrderRequest, array $headers = []): PostOrderResponse|array
    {
        return Actions\CreateOrder::run($this, $postOrderRequest, $headers);
    }

    /**
     * @param PostOrderRequest $postOrderRequest
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function createOrderAsync(PostOrderRequest $postOrderRequest, array $headers = []): mixed
    {
        return Actions\CreateOrder::runAsync($this, $postOrderRequest, $headers);
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

    /**
     * Deletes an order.
     *
     * @param string $orderId The order ID to delete
     * @param array<string, string> $headers Optional request headers
     * @throws TalerException
     * @throws \Throwable
     */
    public function deleteOrder(string $orderId, array $headers = []): void
    {
        Actions\DeleteOrder::run($this, $orderId, $headers);
    }

    /**
     * Deletes an order asynchronously.
     *
     * @param string $orderId The order ID to delete
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function deleteOrderAsync(string $orderId, array $headers = []): mixed
    {
        return Actions\DeleteOrder::runAsync($this, $orderId, $headers);
    }

    /**
     * Sends a forget request for a specific order.
     *
     * @param string $orderId The order ID to forget fields for
     * @param ForgetRequest $forgetRequest The forget request data
     * @param array<string, string> $headers Optional request headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     */
    public function forgetOrder(string $orderId, ForgetRequest $forgetRequest, array $headers = []): void
    {
        Actions\ForgetOrder::run($this, $orderId, $forgetRequest, $headers);
    }

    /**
     * Sends a forget request for a specific order asynchronously.
     *
     * @param string $orderId The order ID to forget fields for
     * @param ForgetRequest $forgetRequest The forget request data
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function forgetOrderAsync(string $orderId, ForgetRequest $forgetRequest, array $headers = []): mixed
    {
        return Actions\ForgetOrder::runAsync($this, $orderId, $forgetRequest, $headers);
    }
}
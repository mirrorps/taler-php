<?php

namespace Taler\Api\Order\Actions;

use Taler\Api\Order\Dto\MerchantRefundResponse;
use Taler\Api\Order\Dto\RefundRequest;
use Taler\Api\Order\OrderClient;
use Taler\Exception\TalerException;
use Psr\Http\Message\ResponseInterface;

class Refunds
{
    public function __construct(
        private OrderClient $orderClient
    ) {}

    /**
     * Initiates a refund for a specific order.
     *
     * @param OrderClient $orderClient
     * @param string $orderId The order ID to refund
     * @param RefundRequest $refundRequest The refund request data
     * @param array<string, string> $headers Optional request headers
     * @return MerchantRefundResponse|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(
        OrderClient $orderClient,
        string $orderId,
        RefundRequest $refundRequest,
        array $headers = []
    ): MerchantRefundResponse|array {
        $refunds = new self($orderClient);

        try {
            $requestBody = json_encode([
                'refund' => $refundRequest->refund,
                'reason' => $refundRequest->reason
            ], JSON_THROW_ON_ERROR);

            $refunds->orderClient->setResponse(
                $refunds->orderClient->getClient()->request(
                    "POST",
                    "sandbox/private/orders/{$orderId}/refund",
                    $headers,
                    $requestBody
                )
            );

            /** @var MerchantRefundResponse|array{
             *     taler_refund_uri: string,
             *     h_contract: string
             * } $result */
            $result = $orderClient->handleWrappedResponse($refunds->handleResponse(...));

            return $result;
        } catch (TalerException $e) {
            //--- // NOTE: Logging is not necessary here; TalerException is already logged in HttpClientWrapper::run.
            throw $e;
        }
        catch (\Throwable $e) {
            $orderClient->getTaler()->getLogger()->error("Taler refund request failed: {$e->getCode()}, {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Initiates a refund for a specific order asynchronously.
     *
     * @param OrderClient $orderClient
     * @param string $orderId The order ID to refund
     * @param RefundRequest $refundRequest The refund request data
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public static function runAsync(
        OrderClient $orderClient,
        string $orderId,
        RefundRequest $refundRequest,
        array $headers = []
    ): mixed {
        $refunds = new self($orderClient);

        $requestBody = json_encode([
            'refund' => $refundRequest->refund,
            'reason' => $refundRequest->reason
        ], JSON_THROW_ON_ERROR);

        return $orderClient
            ->getClient()
            ->requestAsync(
                "POST",
                "sandbox/private/orders/{$orderId}/refund",
                $headers,
                $requestBody
            )
            ->then(function (ResponseInterface $response) use ($refunds) {
                $refunds->orderClient->setResponse($response);
                return $refunds->orderClient->handleWrappedResponse($refunds->handleResponse(...));
            });
    }

    /**
     * Handles the response from the refund request.
     *
     * @param ResponseInterface $response
     * @return MerchantRefundResponse
     * @throws TalerException
     */
    private function handleResponse(ResponseInterface $response): MerchantRefundResponse
    {
        $data = $this->orderClient->parseResponseBody($response, 200);
        return MerchantRefundResponse::createFromArray($data);
    }
} 
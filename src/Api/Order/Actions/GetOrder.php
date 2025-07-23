<?php

namespace Taler\Api\Order\Actions;

use Taler\Api\Order\Dto\CheckPaymentPaidResponse;
use Taler\Api\Order\OrderClient;
use Taler\Exception\TalerException;
use Psr\Http\Message\ResponseInterface;
use Taler\Api\Order\Dto\CheckPaymentClaimedResponse;
use Taler\Api\Order\Dto\CheckPaymentUnpaidResponse;

class GetOrder
{
    public function __construct(
        private OrderClient $orderClient
    ) {}

    /**
     * @param OrderClient $orderClient
     * @param string $orderId
     * @param array<string, string> $params HTTP params
     * @param array<string, string> $headers Optional request headers
     * @return CheckPaymentPaidResponse|CheckPaymentClaimedResponse|CheckPaymentUnpaidResponse|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(
        OrderClient $orderClient,
        string $orderId,
        array $params = [],
        array $headers = []
    ): CheckPaymentPaidResponse|CheckPaymentClaimedResponse|CheckPaymentUnpaidResponse|array
    {
        $getOrder = new self($orderClient);

        try {
            $getOrder->orderClient->setResponse(
                $getOrder->orderClient->getClient()->request('GET', "private/orders/{$orderId}?" . http_build_query($params), $headers)
            );

            /** @var CheckPaymentPaidResponse|array{
             *     order_id: string,
             *     row_id: int,
             *     timestamp: array{t_s: int},
             *     amount: string,
             *     summary: string,
             *     refundable: bool,
             *     paid: bool
             * } $result */
            $result = $orderClient->handleWrappedResponse($getOrder->handleResponse(...));

            return $result;
        } catch (TalerException $e) {
            //--- NOTE: no need to log here, TalerException is already logged in HttpClientWrapper::run
            throw $e;
        }
        catch (\Throwable $e) {
            $orderClient->getTaler()->getLogger()->error("Taler get order request failed: {$e->getCode()}, {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * @param OrderClient $orderClient
     * @param string $orderId
     * @param array<string, string> $params HTTP params
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public static function runAsync(
        OrderClient $orderClient,
        string $orderId,
        array $params = [],
        array $headers = []
    ): mixed {
        $getOrder = new self($orderClient);

        return $orderClient
            ->getClient()
            ->requestAsync('GET', "private/orders/{$orderId}?" . http_build_query($params), $headers)
            ->then(function (ResponseInterface $response) use ($getOrder) {
                $getOrder->orderClient->setResponse($response);
                return $getOrder->orderClient->handleWrappedResponse($getOrder->handleResponse(...));
            });
    }

    private function handleResponse(ResponseInterface $response): CheckPaymentPaidResponse|CheckPaymentClaimedResponse|CheckPaymentUnpaidResponse
    {
        $data = $this->orderClient->parseResponseBody($response, 200);

        return match ($data['order_status']) {
            'paid'    => CheckPaymentPaidResponse::createFromArray($data),
            'claimed' => CheckPaymentClaimedResponse::createFromArray($data),
            'unpaid'  => CheckPaymentUnpaidResponse::createFromArray($data),
            default   => throw new TalerException('Invalid order status: ' . $data['order_status']),
        };
    }
}
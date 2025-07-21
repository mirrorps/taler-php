<?php

namespace Taler\Api\Order\Actions;

use Taler\Api\Order\Dto\CheckPaymentPaidResponse;
use Taler\Api\Order\OrderClient;
use Taler\Exception\TalerException;
use Psr\Http\Message\ResponseInterface;

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
     * @return CheckPaymentPaidResponse|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(
        OrderClient $orderClient,
        string $orderId,
        array $params = [],
        array $headers = []
    ): CheckPaymentPaidResponse|array
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

    private function handleResponse(ResponseInterface $response): CheckPaymentPaidResponse
    {
        $data = $this->orderClient->parseResponseBody($response, 200);

        return CheckPaymentPaidResponse::createFromArray($data);
    }
}
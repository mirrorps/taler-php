<?php

namespace Taler\Api\Order\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Order\OrderClient;
use Taler\Api\Order\Dto\GetOrdersRequest;
use Taler\Api\Order\Dto\OrderHistory;
use Taler\Exception\TalerException;
use Taler\Api\Order\Dto\Amount;

class GetOrders
{
    public function __construct(
        private OrderClient $orderClient
    ) {}

    /**
     * @param GetOrdersRequest|array<string, scalar>|null $request Query parameters (typed DTO preferred)
     * @param array<string, string> $headers HTTP headers
     * @return OrderHistory|array{
     *     orders: array<array{
     *         order_id: string,
     *         row_id: int,
     *         timestamp: array{t_s: int},
     *         amount: string,
     *         summary: string,
     *         refundable: bool,
     *         paid: bool
     *     }>
     * }
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(
        OrderClient $orderClient,
        GetOrdersRequest|array|null $request = null,
        array $headers = []
    ): OrderHistory|array
    {
        $getOrders = new self($orderClient);

        try {
            $params = $request instanceof GetOrdersRequest ? $request->toArray() : ($request ?? []);
            $getOrders->orderClient->setResponse(
                $getOrders->orderClient->getClient()->request('GET', 'private/orders?' . http_build_query($params), $headers)
            );

            /** @var OrderHistory|array{
             *     orders: array<array{
             *         order_id: string,
             *         row_id: int,
             *         timestamp: array{t_s: int},
             *         amount: string,
             *         summary: string,
             *         refundable: bool,
             *         paid: bool
             *     }>
             * } $result */
            $result = $orderClient->handleWrappedResponse($getOrders->handleResponse(...));

            return $result;
        } catch (TalerException $e) {
            //--- NOTE: no need to log here, TalerException is already logged in HttpClientWrapper::run
            throw $e;
        }
        catch (\Throwable $e) {
            $sanitized = \Taler\Helpers\sanitizeString((string) $e->getMessage());
            $orderClient->getTaler()->getLogger()->error("Taler get orders request failed: {$e->getCode()}, {$sanitized}");
            throw $e;
        }
    }

    /**
     * Handle the orders response and return the appropriate DTO
     */
    private function handleResponse(ResponseInterface $response): OrderHistory
    {
        /** @var array{
         *     orders: array<array{
         *         order_id: string,
         *         row_id: int,
         *         timestamp: array{t_s: int},
         *         amount: string,
         *         summary: string,
         *         refundable: bool,
         *         paid: bool
         *     }>
         * } $data */
        $data = $this->orderClient->parseResponseBody($response, 200);

        return OrderHistory::createFromArray($data);
    }

    /**
     * @param GetOrdersRequest|array<string, scalar>|null $request Query parameters (typed DTO preferred)
     * @param array<string, string> $headers HTTP headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public static function runAsync(
        OrderClient $orderClient,
        GetOrdersRequest|array|null $request = null,
        array $headers = []
    ): mixed
    {
        $getOrders = new self($orderClient);
        $params = $request instanceof GetOrdersRequest ? $request->toArray() : ($request ?? []);

        return $orderClient
            ->getClient()
            ->requestAsync('GET', 'private/orders?' . http_build_query($params), $headers)
            ->then(function (ResponseInterface $response) use ($getOrders) {
                $getOrders->orderClient->setResponse($response);
                return $getOrders->orderClient->handleWrappedResponse($getOrders->handleResponse(...));
            });
    }
}
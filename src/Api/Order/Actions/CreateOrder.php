<?php

namespace Taler\Api\Order\Actions;

use Taler\Api\Order\Dto\PostOrderRequest;
use Taler\Api\Order\Dto\PostOrderResponse;
use Taler\Api\Order\OrderClient;
use Taler\Exception\TalerException;
use Psr\Http\Message\ResponseInterface;
use Taler\Exception\OutOfStockException;
use Taler\Exception\PaymentDeniedLegallyException;

class CreateOrder
{
    public function __construct(
        private OrderClient $orderClient
    ) {}

    /**
     * Creates a new order.
     *
     * @param OrderClient $orderClient
     * @param PostOrderRequest $postOrderRequest The order request data
     * @param array<string, string> $headers Optional request headers
     * @return PostOrderResponse|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(
        OrderClient $orderClient,
        PostOrderRequest $postOrderRequest,
        array $headers = []
    ): PostOrderResponse|array {
        $createOrder = new self($orderClient);

        try {
            $requestBody = json_encode($postOrderRequest, JSON_THROW_ON_ERROR);

            $createOrder->orderClient->setResponse(
                $createOrder->orderClient->getClient()->request(
                    "POST",
                    "private/orders",
                    $headers,
                    $requestBody
                )
            );

            /** @var PostOrderResponse|array{
             *     order_id: string,
             *     token?: string
             * } $result */
            $result = $orderClient->handleWrappedResponse($createOrder->handleResponse(...));

            return $result;
        } catch (TalerException $e) {
            //--- // NOTE: Logging is not necessary here; TalerException is already logged in HttpClientWrapper::run.
            throw $e;
        }
        catch (\Throwable $e) {
            $sanitized = \Taler\Helpers\sanitizeString((string) $e->getMessage());
            $orderClient->getTaler()->getLogger()->error("Taler create order request failed: {$e->getCode()}, {$sanitized}");
            throw $e;
        }
    }

    /**
     * Creates a new order asynchronously.
     *
     * @param OrderClient $orderClient
     * @param PostOrderRequest $postOrderRequest The order request data
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public static function runAsync(
        OrderClient $orderClient,
        PostOrderRequest $postOrderRequest,
        array $headers = []
    ): mixed {
        $createOrder = new self($orderClient);

        $requestBody = json_encode($postOrderRequest, JSON_THROW_ON_ERROR);

        return $orderClient
            ->getClient()
            ->requestAsync(
                "POST",
                "private/orders",
                $headers,
                $requestBody
            )
            ->then(function (ResponseInterface $response) use ($createOrder) {
                $createOrder->orderClient->setResponse($response);
                return $createOrder->orderClient->handleWrappedResponse($createOrder->handleResponse(...));
            });
    }

    /**
     * Handles the response from the create order request.
     *
     * @param ResponseInterface $response
     * @return PostOrderResponse
     * @throws TalerException|OutOfStockException
     */
    private function handleResponse(ResponseInterface $response): PostOrderResponse
    {
        try {
            $data = $this->orderClient->parseResponseBody($response, 200);
            return PostOrderResponse::createFromArray($data);
        } catch (TalerException $e) {
            match ($e->getCode()) {
                410 => throw new OutOfStockException(response: $response),
                451 => throw new PaymentDeniedLegallyException(response: $response),
                default => throw $e,
            };
        }
    }
}
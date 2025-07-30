<?php

namespace Taler\Api\Order\Actions;

use Taler\Api\Order\OrderClient;
use Taler\Exception\TalerException;
use Psr\Http\Message\ResponseInterface;

class DeleteOrder
{
    public function __construct(
        private OrderClient $orderClient
    ) {}

    /**
     * Deletes a specific order.
     *
     * @param OrderClient $orderClient
     * @param string $orderId The order ID to delete
     * @param array<string, string> $headers Optional request headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(
        OrderClient $orderClient,
        string $orderId,
        array $headers = []
    ): void {
        $delete = new self($orderClient);

        try {
            $delete->orderClient->setResponse(
                $delete->orderClient->getClient()->request(
                    "DELETE",
                    "private/orders/{$orderId}",
                    $headers
                )
            );

            $orderClient->handleWrappedResponse($delete->handleResponse(...));

        } catch (TalerException $e) {
            //--- // NOTE: Logging is not necessary here; TalerException is already logged in HttpClientWrapper::run.
            throw $e;
        }
        catch (\Throwable $e) {
            $orderClient->getTaler()->getLogger()->error("Taler delete order request failed: {$e->getCode()}, {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Deletes a specific order asynchronously.
     *
     * @param OrderClient $orderClient
     * @param string $orderId The order ID to delete
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public static function runAsync(
        OrderClient $orderClient,
        string $orderId,
        array $headers = []
    ): mixed {
        $delete = new self($orderClient);

        return $orderClient
            ->getClient()
            ->requestAsync(
                "DELETE",
                "private/orders/{$orderId}",
                $headers
            )
            ->then(function (ResponseInterface $response) use ($delete) {
                $delete->orderClient->setResponse($response);
                $delete->orderClient->handleWrappedResponse($delete->handleResponse(...));
            });
    }

    /**
     * Handles the response from the delete request.
     *
     * @param ResponseInterface $response
     * @throws TalerException
     */
    private function handleResponse(ResponseInterface $response): void
    {
        $this->orderClient->parseResponseBody($response, 204);
    }
}
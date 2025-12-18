<?php

namespace Taler\Api\Order\Actions;

use Taler\Api\Order\Dto\ForgetRequest;
use Taler\Api\Order\OrderClient;
use Taler\Exception\TalerException;
use Psr\Http\Message\ResponseInterface;

use const Taler\Http\HTTP_STATUS_CODE_NO_CONTENT;
use const Taler\Http\HTTP_STATUS_CODE_SUCCESS;

class ForgetOrder
{
    public function __construct(
        private OrderClient $orderClient
    ) {}

    /**
     * Sends a forget request for a specific order.
     *
     * @param OrderClient $orderClient
     * @param string $orderId The order ID to forget fields for
     * @param ForgetRequest $forgetRequest The forget request DTO
     * @param array<string, string> $headers Optional request headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(
        OrderClient $orderClient,
        string $orderId,
        ForgetRequest $forgetRequest,
        array $headers = []
    ): void {
        $forget = new self($orderClient);

        try {
            $requestBody = json_encode([
                'fields' => $forgetRequest->fields,
            ], JSON_THROW_ON_ERROR);

            $forget->orderClient->setResponse(
                $forget->orderClient->getClient()->request(
                    'PATCH',
                    "private/orders/{$orderId}/forget",
                    $headers,
                    $requestBody
                )
            );

            // Handle wrapped response (expects 204 No Content)
            $orderClient->handleWrappedResponse($forget->handleResponse(...));

        } catch (TalerException $e) {
            // NOTE: Logging is not necessary here; TalerException is already logged in HttpClientWrapper::run.
            throw $e;
        } catch (\Throwable $e) {
            $sanitized = \Taler\Helpers\sanitizeString((string) $e->getMessage());
            $orderClient->getTaler()->getLogger()->error("Taler forget request failed: {$e->getCode()}, {$sanitized}");
            throw $e;
        }
    }

    /**
     * Sends a forget request for a specific order asynchronously.
     *
     * @param OrderClient $orderClient
     * @param string $orderId The order ID to forget fields for
     * @param ForgetRequest $forgetRequest The forget request DTO
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public static function runAsync(
        OrderClient $orderClient,
        string $orderId,
        ForgetRequest $forgetRequest,
        array $headers = []
    ): mixed {
        $forget = new self($orderClient);

        $requestBody = json_encode([
            'fields' => $forgetRequest->fields,
        ], JSON_THROW_ON_ERROR);

        return $orderClient
            ->getClient()
            ->requestAsync(
                'PATCH',
                "private/orders/{$orderId}/forget",
                $headers,
                $requestBody
            )
            ->then(function (ResponseInterface $response) use ($forget) {
                $forget->orderClient->setResponse($response);
                return $forget->orderClient->handleWrappedResponse($forget->handleResponse(...));
            });
    }

    /**
     * Handles the response from the forget request.
     *
     * @param ResponseInterface $response
     * @return void
     * @throws TalerException
     */
    private function handleResponse(ResponseInterface $response): void
    {
        /**
         * Success status codes are 200 and 204.
         * For both, we don't need to decode the body.
         */
        $statusCode = $response->getStatusCode();

        if(in_array($statusCode, [HTTP_STATUS_CODE_SUCCESS, HTTP_STATUS_CODE_NO_CONTENT])) {
            return;
        }

        throw new TalerException(
            message: 'Unexpected response status code: ' . $response->getStatusCode(),
            code: $response->getStatusCode(),
            response: $response
        );
    }
}



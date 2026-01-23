<?php

namespace Taler\Api\Order\Actions;

use Taler\Api\Order\Dto\CheckPaymentPaidResponse;
use Taler\Api\Order\OrderClient;
use Taler\Exception\TalerException;
use Psr\Http\Message\ResponseInterface;
use Taler\Api\Order\Dto\CheckPaymentClaimedResponse;
use Taler\Api\Order\Dto\CheckPaymentUnpaidResponse;
use Taler\Api\Order\Dto\GetOrderRequest;
use Taler\Api\TwoFactorAuth\Dto\ChallengeResponse;

use const Taler\Http\HTTP_STATUS_CODE_ACCEPTED;
use const Taler\Http\HTTP_STATUS_CODE_SUCCESS;

class GetOrder
{
    public function __construct(
        private OrderClient $orderClient
    ) {}

    /**
     * @param OrderClient $orderClient
     * @param string $orderId
     * @param GetOrderRequest|array<string, scalar>|null $request Query parameters (typed DTO preferred)
     * @param array<string, string> $headers Optional request headers
     * @return CheckPaymentPaidResponse|CheckPaymentClaimedResponse|CheckPaymentUnpaidResponse|ChallengeResponse|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(
        OrderClient $orderClient,
        string $orderId,
        GetOrderRequest|array|null $request = null,
        array $headers = []
    ): CheckPaymentPaidResponse|CheckPaymentClaimedResponse|CheckPaymentUnpaidResponse|ChallengeResponse|array
    {
        $getOrder = new self($orderClient);

        try {
            $params = $request instanceof GetOrderRequest ? $request->toArray() : ($request ?? []);
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
            $sanitized = \Taler\Helpers\sanitizeString((string) $e->getMessage());
            $orderClient->getTaler()->getLogger()->error("Taler get order request failed: {$e->getCode()}, {$sanitized}");
            throw $e;
        }
    }

    /**
     * @param OrderClient $orderClient
     * @param string $orderId
     * @param GetOrderRequest|array<string, scalar>|null $request Query parameters (typed DTO preferred)
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public static function runAsync(
        OrderClient $orderClient,
        string $orderId,
        GetOrderRequest|array|null $request = null,
        array $headers = []
    ): mixed {
        $getOrder = new self($orderClient);
        $params = $request instanceof GetOrderRequest ? $request->toArray() : ($request ?? []);

        return $orderClient
            ->getClient()
            ->requestAsync('GET', "private/orders/{$orderId}?" . http_build_query($params), $headers)
            ->then(function (ResponseInterface $response) use ($getOrder) {
                $getOrder->orderClient->setResponse($response);
                return $getOrder->orderClient->handleWrappedResponse($getOrder->handleResponse(...));
            });
    }

    private function handleResponse(ResponseInterface $response): CheckPaymentPaidResponse|CheckPaymentClaimedResponse|CheckPaymentUnpaidResponse|ChallengeResponse
    {
        // $data = $this->orderClient->parseResponseBody($response, 200);

        // try {
        //     $data = json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        // } catch (\JsonException $e) {
        //     throw new TalerException(
        //         message: 'Failed to decode order response JSON: ' . $e->getMessage(),
        //         code: $response->getStatusCode(),
        //         previous: $e,
        //         response: $response
        //     );
        // }

        $data = $this->orderClient->decodeResponseBody($response);

        if($response->getStatusCode() == HTTP_STATUS_CODE_ACCEPTED) {
            return ChallengeResponse::createFromArray($data);
        }

        if($response->getStatusCode() != HTTP_STATUS_CODE_SUCCESS) {
            throw new TalerException(
                message: 'Unexpected response status code: ' . $response->getStatusCode(),
                code: $response->getStatusCode(),
                response: $response
            );
        }

        return match ($data['order_status']) {
            'paid'    => CheckPaymentPaidResponse::createFromArray($data),
            'claimed' => CheckPaymentClaimedResponse::createFromArray($data),
            'unpaid'  => CheckPaymentUnpaidResponse::createFromArray($data),
            default   => throw new TalerException('Invalid order status: ' . $data['order_status']),
        };
    }
}
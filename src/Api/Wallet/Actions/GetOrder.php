<?php

namespace Taler\Api\Wallet\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Wallet\WalletClient;
use Taler\Api\Wallet\Dto\StatusPaidResponse;
use Taler\Api\Wallet\Dto\StatusGotoResponse;
use Taler\Api\Wallet\Dto\StatusUnpaidResponse;
use Taler\Exception\TalerException;

class GetOrder
{
    public function __construct(
        private WalletClient $walletClient
    ) {}

    /**
     * @param string $orderId The ID of the order to retrieve
     * @param array<string, string> $params HTTP params
     * @param array<string, string> $headers HTTP headers
     * @return StatusPaidResponse|StatusGotoResponse|StatusUnpaidResponse
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(
        WalletClient $walletClient,
        string $orderId,
        array $params = [],
        array $headers = []
    ): StatusPaidResponse|StatusGotoResponse|StatusUnpaidResponse
    {
        $getOrder = new self($walletClient);

        try {
            $getOrder->walletClient->setResponse(
                $getOrder->walletClient->getClient()->request('GET', "orders/$orderId?" . http_build_query($params), $headers)
            );

            return $getOrder->walletClient->handleWrappedResponse($getOrder->handleResponse(...));
        } catch (TalerException $e) {
            //--- NOTE: no need to log here, TalerException is already logged in HttpClientWrapper::run
            throw $e;
        }
        catch (\Throwable $e) {
            $walletClient->getTaler()->getLogger()->error("Taler get public order request failed (wallet API): {$e->getCode()}, {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Handle the order response and return the appropriate DTO based on status code
     * 
     * @return StatusPaidResponse|StatusGotoResponse|StatusUnpaidResponse
     */
    private function handleResponse(ResponseInterface $response): StatusPaidResponse|StatusGotoResponse|StatusUnpaidResponse
    {
        $statusCode = $response->getStatusCode();
        $data = json_decode((string)$response->getBody(), true);
        
        return match ($statusCode) {
            200 => StatusPaidResponse::fromArray($data),
            202 => StatusGotoResponse::fromArray($data),
            402 => StatusUnpaidResponse::fromArray($data),
            default => throw new TalerException("Unexpected response status code: $statusCode", $statusCode)
        };
    }

    /**
     * @param string $orderId The ID of the order to retrieve
     * @param array<string, string> $params HTTP params
     * @param array<string, string> $headers HTTP headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public static function runAsync(
        WalletClient $walletClient,
        string $orderId,
        array $params = [],
        array $headers = []
    ): mixed
    {
        $getOrder = new self($walletClient);

        return $walletClient
            ->getClient()
            ->requestAsync('GET', "orders/$orderId?" . http_build_query($params), $headers)
            ->then(function (ResponseInterface $response) use ($getOrder) {
                $getOrder->walletClient->setResponse($response);
                return $getOrder->walletClient->handleWrappedResponse($getOrder->handleResponse(...));
            });
    }
}
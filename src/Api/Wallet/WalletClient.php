<?php

namespace Taler\Api\Wallet;

use Taler\Api\Base\AbstractApiClient;
use Taler\Exception\TalerException;
use Taler\Api\Wallet\Dto\StatusPaidResponse;
use Taler\Api\Wallet\Dto\StatusGotoResponse;
use Taler\Api\Wallet\Dto\StatusUnpaidResponse;

class WalletClient extends AbstractApiClient
{
    /**
     * @param string $orderId The ID of the order to retrieve
     * @param array<string, string> $params HTTP params
     * @param array<string, string> $headers Optional request headers
     * @return StatusPaidResponse|StatusGotoResponse|StatusUnpaidResponse
     * @throws TalerException
     * @throws \Throwable
     */
    public function getOrder(string $orderId, array $params = [], array $headers = []): StatusPaidResponse|StatusGotoResponse|StatusUnpaidResponse
    {
        return Actions\GetOrder::run($this, $orderId, $params, $headers);
    }

    /**
     * @param string $orderId The ID of the order to retrieve
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
}
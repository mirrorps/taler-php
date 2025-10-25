<?php

namespace Taler\Api\Config;

use Taler\Api\Base\AbstractApiClient;
use Taler\Api\Config\Dto\MerchantVersionResponse;
use Taler\Exception\TalerException;

class ConfigClient extends AbstractApiClient
{
    /**
     * @param array<string, string> $headers Optional request headers
     * @return MerchantVersionResponse|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public function getConfig(array $headers = []): MerchantVersionResponse|array
    {
        return Actions\GetConfig::run($this, $headers);
    }

    /**
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     */
    public function getConfigAsync(array $headers = []): mixed
    {
        return Actions\GetConfig::runAsync($this, $headers);
    }
}



<?php

namespace Taler\Api\Exchange\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Exchange\ExchangeClient;
use Taler\Api\Exchange\Dto\ExchangeVersionResponse;
use Taler\Exception\TalerException;

class Config
{
    /**
     * @param array<string, string> $headers HTTP headers
     * @return ExchangeVersionResponse|array{
     *     version: string,
     *     name: string,
     *     currency: string,
     *     currency_specification: array{
     *         name: string,
     *         currency: string,
     *         num_fractional_input_digits: int,
     *         num_fractional_normal_digits: int,
     *         num_fractional_trailing_zero_digits: int,
     *         alt_unit_names: array<string, string>
     *     },
     *     supported_kyc_requirements: array<string, string>,
     *     implementation: string|null,
     *     shopping_url: string|null,
     *     aml_spa_dialect: string|null
     * }
     */
    public static function run(
        ExchangeClient $exchangeClient,
        array $headers = []
    ): ExchangeVersionResponse|array
    {
        try {
            $cacheKey = 'exchange_config';
            
            // If caching is enabled, try to get from cache
            if ($exchangeClient->getTaler()->getCacheWrapper()?->getTtl() !== null) {

                $cachedResult = $exchangeClient->getTaler()->getCacheWrapper()->getCache()->get($cacheKey); //--- @phpstan-ignore-line cannot be null, checked in the if statement above
                if ($cachedResult !== null) {
                    $exchangeClient->getTaler()->getCacheWrapper()->clearCacheSettings();    
                    return $cachedResult;
                }
            }
            
            $exchangeClient->setResponse(
                $exchangeClient->getClient()->request('GET', 'config', $headers)
            );

    /** @var ExchangeVersionResponse|array{
     *     version: string,
     *     name: string,
     *     currency: string,
     *     currency_specification: array{
     *         name: string,
     *         currency: string,
     *         num_fractional_input_digits: int,
     *         num_fractional_normal_digits: int,
     *         num_fractional_trailing_zero_digits: int,
     *         alt_unit_names: array<string, string>
     *     },
     *     supported_kyc_requirements: array<string, string>,
     *     implementation: string|null,
     *     shopping_url: string|null,
     *     aml_spa_dialect: string|null
     * } $result */
            $result = $exchangeClient->handleWrappedResponse(self::handleConfigResponse(...));
            
            // If caching was enabled, store in cache
            if ($exchangeClient->getTaler()->getCacheWrapper()?->getTtl() !== null) {
                $exchangeClient->getTaler()->getCacheWrapper()->getCache()->set(
                    $cacheKey,
                    $result,
                    $exchangeClient->getTaler()->getCacheWrapper()->getTtl()
                );
            }
            
            // Clear cache settings for next call
            $exchangeClient->getTaler()->getCacheWrapper()->clearCacheSettings();
            
            return $result;
        } catch (\Throwable $e) {
            $exchangeClient->getTaler()->getCacheWrapper()->clearCacheSettings();
            throw $e;
        }
        
    }

    /**
     * Handle the config response and return the appropriate DTO
     */
    private static function handleConfigResponse(ResponseInterface $response): ExchangeVersionResponse
    {
        $data = json_decode((string)$response->getBody(), true);

        if ($response->getStatusCode() !== 200) {
            throw new TalerException('Unexpected response status code: ' . $response->getStatusCode());
        }

        return ExchangeVersionResponse::fromArray($data);
    }

    /**
     * @param array<string, string> $headers HTTP headers
     */
    public static function runAsync(
        ExchangeClient $exchangeClient,
        array $headers = []
    ): mixed
    {
        
        return $exchangeClient
            ->getClient()
            ->requestAsync('GET', 'config', $headers)
            ->then(function (ResponseInterface $response) use ($exchangeClient) {
                $exchangeClient->setResponse($response);
                return $exchangeClient->handleWrappedResponse(self::handleConfigResponse(...));
            });
    }
}
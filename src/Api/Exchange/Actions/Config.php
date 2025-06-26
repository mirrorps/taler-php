<?php

namespace Taler\Api\Exchange\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Exchange\ExchangeClient;
use Taler\Api\Exchange\Dto\ExchangeVersionResponse;
use Taler\Exception\TalerException;

class Config
{
    public function __construct(
        private ExchangeClient $exchangeClient
    ) {}

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
        $config = new self($exchangeClient);

        try {
            $cacheWrapper = $exchangeClient->getTaler()->getCacheWrapper();
            $cacheKey = $cacheWrapper?->getCacheKey() ?? "exchange_config_{$exchangeClient->getTaler()->getConfig()->toHash()}";
            
            // If caching is enabled, try to get from cache
            if ($cacheWrapper?->getTtl() !== null) {
                $cachedResult = $cacheWrapper->getCache()->get($cacheKey);
                if ($cachedResult !== null) {
                    $cacheWrapper->clearCacheSettings();
                    return $cachedResult;
                }
            }
            
            $config->exchangeClient->setResponse(
                $config->exchangeClient->getClient()->request('GET', 'config', $headers)
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
            $result = $exchangeClient->handleWrappedResponse($config->handleResponse(...));
            
            // If caching was enabled, store in cache
            if ($cacheWrapper?->getTtl() !== null) {
                $cacheWrapper->getCache()->set(
                    $cacheKey,
                    $result,
                    $cacheWrapper->getTtl()
                );
            }
            
            // Clear cache settings for next call
            $cacheWrapper?->clearCacheSettings();
            
            return $result;
        } catch (TalerException $e) {
            //--- NOTE: no need to log here, TalerException is already logged in HttpClientWrapper::run
            $cacheWrapper?->clearCacheSettings();
            throw $e;
        }
        catch (\Throwable $e) {
            $cacheWrapper?->clearCacheSettings();
            $exchangeClient->getTaler()->getLogger()->error("Taler config request failed: {$e->getCode()}, {$e->getMessage()}");
            throw $e;
        }
        
    }

    /**
     * Handle the config response and return the appropriate DTO
     */
    private function handleResponse(ResponseInterface $response): ExchangeVersionResponse
    {
        /** @var array{
         *     version: string,
         *     name: string,
         *     currency: string,
         *     currency_specification: array{
         *         name: string,
         *         currency: string,
         *         num_fractional_input_digits: int,
         *         num_fractional_normal_digits: int,
         *         num_fractional_trailing_zero_digits: int,
         *         alt_unit_names: array<numeric-string, string>
         *     },
         *     supported_kyc_requirements: array<int, string>,
         *     implementation?: string|null,
         *     shopping_url?: string|null,
         *     aml_spa_dialect?: string|null
         * } $data */
        $data = $this->exchangeClient->parseResponseBody($response, 200);

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
        $config = new self($exchangeClient);

        return $exchangeClient
            ->getClient()
            ->requestAsync('GET', 'config', $headers)
            ->then(function (ResponseInterface $response) use ($config) {
                $config->exchangeClient->setResponse($response);
                return $config->exchangeClient->handleWrappedResponse($config->handleResponse(...));
            });
    }
}
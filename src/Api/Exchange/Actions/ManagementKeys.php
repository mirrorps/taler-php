<?php

namespace Taler\Api\Exchange\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Exchange\ExchangeClient;
use Taler\Api\Dto\FutureKeysResponse;
use Taler\Exception\TalerException;

class ManagementKeys
{
    public function __construct(
        private ExchangeClient $exchangeClient
    ) {}

    /**
     * @param array<string, string> $headers HTTP headers
     * @return FutureKeysResponse|array{
     *     future_denoms: array<int, array{
     *         section_name: string,
     *         value: string,
     *         stamp_start: string,
     *         stamp_expire_withdraw: string,
     *         stamp_expire_deposit: string,
     *         stamp_expire_legal: string,
     *         denom_pub: string,
     *         fee_withdraw: string,
     *         fee_deposit: string,
     *         fee_refresh: string,
     *         fee_refund: string,
     *         denom_secmod_sig: string
     *     }>,
     *     future_signkeys: array<int, array{
     *         key: string,
     *         stamp_start: string,
     *         stamp_expire: string,
     *         stamp_expire_legal: string,
     *         key_secmod_sig: string
     *     }>,
     *     master_pub: string,
     *     denom_secmod_public_key: string,
     *     signkey_secmod_public_key: string
     * }
     */
    public static function run(
        ExchangeClient $exchangeClient,
        array $headers = []
    ): FutureKeysResponse|array {
        
        $keys = new self($exchangeClient);

        try {
            $cacheWrapper = $exchangeClient->getTaler()->getCacheWrapper();
            $cacheKey = $cacheWrapper?->getCacheKey() ?? "exchange_management_keys_{$exchangeClient->getTaler()->getConfig()->toHash()}";
            
            // If caching is enabled, try to get from cache
            if ($cacheWrapper?->getTtl() !== null) {
                $cachedResult = $cacheWrapper->getCache()->get($cacheKey);
                if ($cachedResult !== null) {
                    $cacheWrapper->clearCacheSettings();
                    return $cachedResult;
                }
            }

            $keys->exchangeClient->setResponse(
                $keys->exchangeClient->getClient()->request('GET', 'management/keys', $headers)
            );

            /** @var FutureKeysResponse|array{
             *     future_denoms: array<int, array{
             *         section_name: string,
             *         value: string,
             *         stamp_start: string,
             *         stamp_expire_withdraw: string,
             *         stamp_expire_deposit: string,
             *         stamp_expire_legal: string,
             *         denom_pub: string,
             *         fee_withdraw: string,
             *         fee_deposit: string,
             *         fee_refresh: string,
             *         fee_refund: string,
             *         denom_secmod_sig: string
             *     }>,
             *     future_signkeys: array<int, array{
             *         key: string,
             *         stamp_start: string,
             *         stamp_expire: string,
             *         stamp_expire_legal: string,
             *         key_secmod_sig: string
             *     }>,
             *     master_pub: string,
             *     denom_secmod_public_key: string,
             *     signkey_secmod_public_key: string
             * } $result */
            $result = $keys->exchangeClient->handleWrappedResponse($keys->handleResponse(...));

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
            $cacheWrapper?->clearCacheSettings(); //--- @phpstan-ignore-line
            throw $e;
        }
        catch (\Throwable $e) {
            $cacheWrapper?->clearCacheSettings();
            $sanitized = \Taler\Helpers\sanitizeString((string) $e->getMessage());
            $exchangeClient->getTaler()->getLogger()->error("Taler management keys request failed: {$e->getCode()}, {$sanitized}");
            throw $e;
        }
    }

    /**
     * Handle the management keys response and return the appropriate DTO
     */
    private function handleResponse(ResponseInterface $response): FutureKeysResponse
    {
        /** @var array{
         *     future_denoms: array<int, array{
         *         section_name: string,
         *         value: string,
         *         stamp_start: string,
         *         stamp_expire_withdraw: string,
         *         stamp_expire_deposit: string,
         *         stamp_expire_legal: string,
         *         denom_pub: string,
         *         fee_withdraw: string,
         *         fee_deposit: string,
         *         fee_refresh: string,
         *         fee_refund: string,
         *         denom_secmod_sig: string
         *     }>,
         *     future_signkeys: array<int, array{
         *         key: string,
         *         stamp_start: string,
         *         stamp_expire: string,
         *         stamp_expire_legal: string,
         *         key_secmod_sig: string
         *     }>,
         *     master_pub: string,
         *     denom_secmod_public_key: string,
         *     signkey_secmod_public_key: string
         * } $data */
        $data = $this->exchangeClient->parseResponseBody($response, 200);

        return FutureKeysResponse::createFromArray($data);
    }

    /**
     * @param array<string, string> $headers HTTP headers
     * @return mixed
     */
    public static function runAsync(
        ExchangeClient $exchangeClient,
        array $headers = []
    ): mixed {
        
        $keys = new self($exchangeClient);

        return $keys->exchangeClient
            ->getClient()
            ->requestAsync('GET', 'management/keys', $headers)
            ->then(function (ResponseInterface $response) use ($keys) {
                $keys->exchangeClient->setResponse($response);
                return $keys->exchangeClient->handleWrappedResponse($keys->handleResponse(...));
            });
    }
} 
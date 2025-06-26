<?php

namespace Taler\Api\Exchange\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Exchange\ExchangeClient;
use Taler\Api\Exchange\Dto\TrackTransferResponse;
use Taler\Exception\TalerException;

class Transfer
{
    public function __construct(
        private ExchangeClient $exchangeClient
    ) {}

    /**
     * @param string $wtid The wire transfer identifier
     * @param array<string, string> $headers HTTP headers
     * @return TrackTransferResponse|array{
     *     total: string,
     *     wire_fee: string,
     *     merchant_pub: string,
     *     h_payto: string,
     *     execution_time: array{t_s: int|string},
     *     deposits: array<int, array{
     *         h_contract_terms: string,
     *         coin_pub: string,
     *         deposit_value: string,
     *         deposit_fee: string,
     *         refund_total?: string|null
     *     }>,
     *     exchange_sig: string,
     *     exchange_pub: string
     * }
     * @throws TalerException
     * @throws \Throwable
     * @see https://docs.taler.net/core/api-exchange.html#get--transfers-$WTID
     */
    public static function run(
        ExchangeClient $exchangeClient,
        string $wtid,
        array $headers = []
    ): TrackTransferResponse|array {
        
        $transfer = new self($exchangeClient);

        try {
            $cacheWrapper = $exchangeClient->getTaler()->getCacheWrapper();
            $cacheKey = $cacheWrapper?->getCacheKey() ?? "exchange_transfer_{$wtid}_{$exchangeClient->getTaler()->getConfig()->toHash()}";
            
            // If caching is enabled, try to get from cache
            if ($cacheWrapper?->getTtl() !== null) {
                $cachedResult = $cacheWrapper->getCache()->get($cacheKey);
                if ($cachedResult !== null) {
                    $cacheWrapper->clearCacheSettings();
                    return $cachedResult;
                }
            }

            $transfer->exchangeClient->setResponse(
                $transfer->exchangeClient->getClient()->request('GET', "transfers/{$wtid}", $headers)
            );

            /** @var TrackTransferResponse|array{
             *     total: string,
             *     wire_fee: string,
             *     merchant_pub: string,
             *     h_payto: string,
             *     execution_time: array{t_s: int|string},
             *     deposits: array<int, array{
             *         h_contract_terms: string,
             *         coin_pub: string,
             *         deposit_value: string,
             *         deposit_fee: string,
             *         refund_total?: string|null
             *     }>,
             *     exchange_sig: string,
             *     exchange_pub: string
             * } $result */
            $result = $transfer->exchangeClient->handleWrappedResponse($transfer->handleResponse(...));

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
            $exchangeClient->getTaler()->getLogger()->error("Taler transfer request failed: {$e->getCode()}, {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Handle the transfer response and return the appropriate DTO
     */
    private function handleResponse(ResponseInterface $response): TrackTransferResponse
    {
        /** @var array{
         *     total: string,
         *     wire_fee: string,
         *     merchant_pub: string,
         *     h_payto: string,
         *     execution_time: array{t_s: int|string},
         *     deposits: array<int, array{
         *         h_contract_terms: string,
         *         coin_pub: string,
         *         deposit_value: string,
         *         deposit_fee: string,
         *         refund_total?: string|null
         *     }>,
         *     exchange_sig: string,
         *     exchange_pub: string
         * } $data */
        $data = $this->exchangeClient->parseResponseBody($response, 200);

        return TrackTransferResponse::fromArray($data);
    }

    /**
     * @param string $wtid The wire transfer identifier
     * @param array<string, string> $headers HTTP headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     * @see https://docs.taler.net/core/api-exchange.html#get--transfers-$WTID
     */
    public static function runAsync(
        ExchangeClient $exchangeClient,
        string $wtid,
        array $headers = []
    ): mixed {
        
        $transfer = new self($exchangeClient);

        return $transfer->exchangeClient
            ->getClient()
            ->requestAsync('GET', "transfers/{$wtid}", $headers)
            ->then(function (ResponseInterface $response) use ($transfer) {
                $transfer->exchangeClient->setResponse($response);
                return $transfer->exchangeClient->handleWrappedResponse($transfer->handleResponse(...));
            });
    }
} 
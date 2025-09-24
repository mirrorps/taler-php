<?php

namespace Taler\Api\Exchange\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Dto\ErrorDetail;
use Taler\Api\Exchange\ExchangeClient;
use Taler\Api\Exchange\Dto\TrackTransactionResponse;
use Taler\Api\Exchange\Dto\TrackTransactionAcceptedResponse;
use Taler\Exception\TalerException;

class Deposits
{
    public function __construct(
        private ExchangeClient $exchangeClient
    ) {}

    /**
     * @param string $H_WIRE (the hash of the merchant's payment details)
     * @param string $MERCHANT_PUB (the merchant's public key (EdDSA))
     * @param string $H_CONTRACT_TERMS (the hash of the contract terms that were paid)
     * @param string $COIN_PUB (the public key of the coin used for the payment)
     * @param string $merchant_sig (EdDSA signature of the merchant made with purpose TALER_SIGNATURE_MERCHANT_TRACK_TRANSACTION over a TALER_DepositTrackPS, affirming that it is really the merchant who requires obtaining the wire transfer identifier)
     * @param string|null $timeout_ms [Optional] (If specified, the exchange will wait up to NUMBER milliseconds for completion of a deposit operation before sending the HTTP response)
     * @param int|null $lpt [Optional] (Specifies what status change we are long-polling for. Use 1 to wait for the a 202 state where kyc_ok is false or a 200 OK response. 2 to wait exclusively for a 200 OK response)
     * @param array<string, string> $headers HTTP headers
     * @return TrackTransactionResponse|TrackTransactionAcceptedResponse|ErrorDetail|array{
     *     wtid?: string,
     *     execution_time: array{t_s: int|string},
     *     coin_contribution?: string,
     *     exchange_sig?: string,
     *     exchange_pub?: string,
     *     requirement_row?: int|null,
     *     kyc_ok?: bool,
     *     account_pub?: string|null,
     *     code?: int,
     *     hint?: string|null,
     *     detail?: string|null
     * }
     * @throws TalerException
     * @throws \Throwable
     * @see https://docs.taler.net/core/api-exchange.html#get--deposits-$H_WIRE-$MERCHANT_PUB-$H_CONTRACT_TERMS-$COIN_PUB
     */
    public static function run(
        ExchangeClient $exchangeClient,
        string $H_WIRE,
        string $MERCHANT_PUB,
        string $H_CONTRACT_TERMS,
        string $COIN_PUB,
        string $merchant_sig,
        ?string $timeout_ms = null,
        ?int $lpt = null,
        array $headers = []
    ): TrackTransactionResponse|TrackTransactionAcceptedResponse|ErrorDetail|array {
        
        $deposits = new self($exchangeClient);

        try {
            $cacheWrapper = $exchangeClient->getTaler()->getCacheWrapper();
            $cacheKey = $cacheWrapper?->getCacheKey() ?? "exchange_deposits_{$exchangeClient->getTaler()->getConfig()->toHash()}";
            
            // If caching is enabled, try to get from cache
            if ($cacheWrapper?->getTtl() !== null) {
                $cachedResult = $cacheWrapper->getCache()->get($cacheKey);
                if ($cachedResult !== null) {
                    $cacheWrapper->clearCacheSettings();
                    return $cachedResult;
                }
            }

            $deposits->exchangeClient->setResponse(
                $deposits->exchangeClient->getClient()->request(
                    'GET',
                    "deposits/{$H_WIRE}/{$MERCHANT_PUB}/{$H_CONTRACT_TERMS}/{$COIN_PUB}?merchant_sig={$merchant_sig}&timeout_ms={$timeout_ms}&lpt={$lpt}",
                    $headers
                )
            );

            /** @var TrackTransactionResponse|TrackTransactionAcceptedResponse|ErrorDetail|array{
             *     wtid?: string,
             *     execution_time: array{t_s: int|string},
             *     coin_contribution?: string,
             *     exchange_sig?: string,
             *     exchange_pub?: string,
             *     requirement_row?: int|null,
             *     kyc_ok?: bool,
             *     account_pub?: string|null,
             *     code?: int,
             *     hint?: string|null,
             *     detail?: string|null
             * } $result */
            $result = $deposits->exchangeClient->handleWrappedResponse($deposits->handleResponse(...));

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
            $cacheWrapper?->clearCacheSettings(); // @phpstan-ignore-line
            throw $e;
        }
        catch (\Throwable $e) {
            $cacheWrapper?->clearCacheSettings();
            $sanitized = \Taler\Helpers\sanitizeString((string) $e->getMessage());
            $exchangeClient->getTaler()->getLogger()->error("Taler deposits request failed: {$e->getCode()}, {$sanitized}");
            throw $e;
        }
    }

    /**
     * Handle the deposits response and return the appropriate DTO
     */
    private function handleResponse(ResponseInterface $response): TrackTransactionResponse|TrackTransactionAcceptedResponse|ErrorDetail
    {
        
            /** @var array{
             *     wtid: string,
             *     execution_time: array{t_s: int|string},
             *     coin_contribution: string,
             *     exchange_sig: string,
             *     exchange_pub: string
             * }|array{
             *     requirement_row?: int|null,
             *     kyc_ok: bool,
             *     execution_time: array{t_s: int|string},
             *     account_pub?: string|null
             * }|array{
             *     code: int,
             *     hint?: string|null,
             *     detail?: string|null,
             *     parameter?: string|null,
             *     path?: string|null,
             *     offset?: string|null,
             *     index?: string|null,
             *     object?: string|null,
             *     currency?: string|null,
             *     type_expected?: string|null,
             *     type_actual?: string|null,
             *     extra?: array<string, mixed>|null
             * } $data */
        
        $data = json_decode((string)$response->getBody(), true);
        
        return match ($response->getStatusCode()) {            
            200 => TrackTransactionResponse::fromArray($data),//--- @phpstan-ignore-line
            202 => TrackTransactionAcceptedResponse::fromArray($data),//--- @phpstan-ignore-line
            403 => ErrorDetail::fromArray($data),//--- @phpstan-ignore-line
            default => throw new TalerException('Unexpected response status code: ' . $response->getStatusCode())
        };
    }

    /**
     * @param string $H_WIRE (the hash of the merchant's payment details)
     * @param string $MERCHANT_PUB (the merchant's public key (EdDSA))
     * @param string $H_CONTRACT_TERMS (the hash of the contract terms that were paid)
     * @param string $COIN_PUB (the public key of the coin used for the payment)
     * @param string $merchant_sig (EdDSA signature of the merchant made with purpose TALER_SIGNATURE_MERCHANT_TRACK_TRANSACTION over a TALER_DepositTrackPS, affirming that it is really the merchant who requires obtaining the wire transfer identifier)
     * @param string|null $timeout_ms [Optional] (If specified, the exchange will wait up to NUMBER milliseconds for completion of a deposit operation before sending the HTTP response)
     * @param int|null $lpt [Optional] (Specifies what status change we are long-polling for. Use 1 to wait for the a 202 state where kyc_ok is false or a 200 OK response. 2 to wait exclusively for a 200 OK response)
     * @param array<string, string> $headers HTTP headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     * @see https://docs.taler.net/core/api-exchange.html#get--deposits-$H_WIRE-$MERCHANT_PUB-$H_CONTRACT_TERMS-$COIN_PUB
     */
    public static function runAsync(
        ExchangeClient $exchangeClient,
        string $H_WIRE,
        string $MERCHANT_PUB,
        string $H_CONTRACT_TERMS,
        string $COIN_PUB,
        string $merchant_sig,
        ?string $timeout_ms = null,
        ?int $lpt = null,
        array $headers = []
    ): mixed {
        
        $deposits = new self($exchangeClient);

        return $deposits->exchangeClient
            ->getClient()
            ->requestAsync(
                'GET',
                "deposits/{$H_WIRE}/{$MERCHANT_PUB}/{$H_CONTRACT_TERMS}/{$COIN_PUB}?merchant_sig={$merchant_sig}&timeout_ms={$timeout_ms}&lpt={$lpt}",
                $headers
            )
            ->then(function (ResponseInterface $response) use ($deposits) {
                $deposits->exchangeClient->setResponse($response);
                return $deposits->exchangeClient->handleWrappedResponse($deposits->handleResponse(...));
            });
    }
} 
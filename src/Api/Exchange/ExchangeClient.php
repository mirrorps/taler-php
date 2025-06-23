<?php

namespace Taler\Api\Exchange;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Base\AbstractApiClient;
use Taler\Api\Dto\ErrorDetail;
use Taler\Api\Dto\FutureKeysResponse;
use Taler\Api\Exchange\Dto\ExchangeKeysResponse;
use Taler\Api\Exchange\Dto\ExchangeVersionResponse;
use Taler\Api\Exchange\Dto\TrackTransactionAcceptedResponse;
use Taler\Api\Exchange\Dto\TrackTransactionResponse;
use Taler\Api\Exchange\Dto\TrackTransferResponse;
use Taler\Exception\TalerException;

class ExchangeClient extends AbstractApiClient
{
    /**
     * @param array<string, string> $headers Optional request headers
     * @return ExchangeVersionResponse|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public function getConfig(array $headers = []): ExchangeVersionResponse|array
    {
        return Actions\Config::run($this, $headers);
    }

    /**
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function getConfigAsync(array $headers = []): mixed
    {
        return Actions\Config::runAsync($this, $headers);
    }

    /**
     * @param array<string, string> $headers Optional request headers
     * @return ExchangeKeysResponse|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public function getKeys(array $headers = []): ExchangeKeysResponse|array
    {
        $this->setResponse(
            $this->getClient()->request('GET', 'keys', $headers)
        );

        return $this->handleWrappedResponse($this->handleKeysResponse(...));
    }

    /**
     * Handle the keys response and return the appropriate DTO
     */
    private function handleKeysResponse(ResponseInterface $response): ExchangeKeysResponse
    {
        $data = json_decode((string)$response->getBody(), true);

        if ($response->getStatusCode() !== 200) {
            throw new TalerException('Unexpected response status code: ' . $response->getStatusCode());
        }

        return ExchangeKeysResponse::fromArray($data);
    }

    /**
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function getKeysAsync(array $headers = []): mixed
    {
        return $this->getClient()
            ->requestAsync('GET', 'keys', $headers)
            ->then(function (ResponseInterface $response) {
                $data = json_decode($response->getBody()->getContents(), true);
                return ExchangeKeysResponse::fromArray($data);
            });
    }

    /**
     * @param array<string, string> $headers Optional request headers
     * @return FutureKeysResponse|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public function getManagementKeys(array $headers = []): FutureKeysResponse|array
    {
        $this->setResponse(
            $this->getClient()->request('GET', 'management/keys', $headers)
        );

        return $this->handleWrappedResponse($this->handleManagementKeysResponse(...));
    }

    /**
     * Handle the management keys response and return the appropriate DTO
     */
    private function handleManagementKeysResponse(ResponseInterface $response): FutureKeysResponse
    {
        $data = json_decode((string)$response->getBody(), true);

        if ($response->getStatusCode() !== 200) {
            throw new TalerException('Unexpected response status code: ' . $response->getStatusCode());
        }

        return FutureKeysResponse::createFromArray($data);
    }

    /**
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function getManagementKeysAsync(array $headers = []): mixed
    {
        return $this->getClient()
            ->requestAsync('GET', 'management/keys', $headers)
            ->then(function (ResponseInterface $response) {
                $data = json_decode($response->getBody()->getContents(), true);
                return FutureKeysResponse::createFromArray($data);
            });
    }

    /**
     * @param string $wtid The wire transfer identifier
     * @param array<string, string> $headers Optional request headers
     * @return TrackTransferResponse|array<string, mixed>
     *
     * @throws TalerException
     * @throws \Throwable
     * @see https://docs.taler.net/core/api-exchange.html#get--transfers-$WTID
     */
    public function getTransfer(string $wtid, array $headers = []): TrackTransferResponse|array
    {
        $this->setResponse(
            $this->getClient()->request('GET', "transfers/{$wtid}", $headers)
        );

        return $this->handleWrappedResponse($this->handleTransferResponse(...));
    }

    /**
     * @param string $wtid The wire transfer identifier
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     *
     * @throws TalerException
     * @throws \Throwable
     * @see https://docs.taler.net/core/api-exchange.html#get--transfers-$WTID
     */
    public function getTransferAsync(string $wtid, array $headers = []): mixed
    {
        return $this->getClient()
            ->requestAsync('GET', "transfers/{$wtid}", $headers)
            ->then(function (ResponseInterface $response) {
                $data = json_decode($response->getBody()->getContents(), true);
                return TrackTransferResponse::fromArray($data);
            });
    }

    /**
     * Handle the transfer response and return the appropriate DTO
     */
    private function handleTransferResponse(ResponseInterface $response): TrackTransferResponse
    {
        $data = json_decode((string)$response->getBody(), true);

        if ($response->getStatusCode() !== 200) {
            throw new TalerException('Unexpected response status code: ' . $response->getStatusCode());
        }

        return TrackTransferResponse::fromArray($data);
    }

    /**
     * @param string $H_WIRE (the hash of the merchant's payment details)
     * @param string $MERCHANT_PUB (the merchant's public key (EdDSA))
     * @param string $H_CONTRACT_TERMS (the hash of the contract terms that were paid)
     * @param string $COIN_PUB (the public key of the coin used for the payment)
     *
     * Query params:
     *
     * @param string $merchant_sig (EdDSA signature of the merchant made with purpose TALER_SIGNATURE_MERCHANT_TRACK_TRANSACTION over a TALER_DepositTrackPS, affirming that it is really the merchant who requires obtaining the wire transfer identifier)
     * @param string|null $timeout_ms [Optional] (If specified, the exchange will wait up to NUMBER milliseconds for completion of a deposit operation before sending the HTTP response)
     * @param int|null $lpt [Optional] (Specifies what status change we are long-polling for. Use 1 to wait for the a 202 state where kyc_ok is false or a 200 OK response. 2 to wait exclusively for a 200 OK response)
     * @param array<string, string> $headers Optional request headers
     *
     * @return TrackTransactionResponse|TrackTransactionAcceptedResponse|ErrorDetail|array<string, mixed>
     *
     * @throws TalerException
     * @throws \Throwable
     * @see https://docs.taler.net/core/api-exchange.html#get--deposits-$H_WIRE-$MERCHANT_PUB-$H_CONTRACT_TERMS-$COIN_PUB
     */
    public function getDeposits(
        string $H_WIRE,
        string $MERCHANT_PUB,
        string $H_CONTRACT_TERMS,
        string $COIN_PUB,
        string $merchant_sig,
        ?string $timeout_ms = null,
        ?int $lpt = null,
        array $headers = []
    ): TrackTransactionResponse|TrackTransactionAcceptedResponse|ErrorDetail|array
    {
        $this->setResponse(
            $this->getClient()->request('GET', "deposits/{$H_WIRE}/{$MERCHANT_PUB}/{$H_CONTRACT_TERMS}/{$COIN_PUB}?merchant_sig={$merchant_sig}&timeout_ms={$timeout_ms}&lpt={$lpt}", $headers)
        );
        
        return $this->handleWrappedResponse($this->handleDepositsResponse(...));
    }

    /**
     * @param string $H_WIRE (the hash of the merchant's payment details)
     * @param string $MERCHANT_PUB (the merchant's public key (EdDSA))
     * @param string $H_CONTRACT_TERMS (the hash of the contract terms that were paid)
     * @param string $COIN_PUB (the public key of the coin used for the payment)
     *
     * Query params:
     *
     * @param string $merchant_sig (EdDSA signature of the merchant made with purpose TALER_SIGNATURE_MERCHANT_TRACK_TRANSACTION over a TALER_DepositTrackPS, affirming that it is really the merchant who requires obtaining the wire transfer identifier)
     * @param string|null $timeout_ms [Optional] (If specified, the exchange will wait up to NUMBER milliseconds for completion of a deposit operation before sending the HTTP response)
     * @param int|null $lpt [Optional] (Specifies what status change we are long-polling for. Use 1 to wait for the a 202 state where kyc_ok is false or a 200 OK response. 2 to wait exclusively for a 200 OK response)
     * @param array<string, string> $headers Optional request headers
     *
     * @return mixed
     *
     * @throws TalerException
     * @throws \Throwable
     * @see https://docs.taler.net/core/api-exchange.html#get--deposits-$H_WIRE-$MERCHANT_PUB-$H_CONTRACT_TERMS-$COIN_PUB
     */
    public function getDepositsAsync(
        string $H_WIRE,
        string $MERCHANT_PUB,
        string $H_CONTRACT_TERMS,
        string $COIN_PUB,
        string $merchant_sig,
        ?string $timeout_ms = null,
        ?int $lpt = null,
        array $headers = []
    ): mixed
    {
        return $this->getClient()
            ->requestAsync('GET', "deposits/{$H_WIRE}/{$MERCHANT_PUB}/{$H_CONTRACT_TERMS}/{$COIN_PUB}?merchant_sig={$merchant_sig}&timeout_ms={$timeout_ms}&lpt={$lpt}", $headers)
            ->then(fn (ResponseInterface $response) => $this->handleDepositsResponse($response));
    }

    /**
     * Handle the deposits response and return the appropriate DTO
     */
    private function handleDepositsResponse(ResponseInterface $response): TrackTransactionResponse|TrackTransactionAcceptedResponse|ErrorDetail
    {
        $data = json_decode((string)$response->getBody(), true);
        
        return match ($response->getStatusCode()) {
            200 => TrackTransactionResponse::fromArray($data),
            202 => TrackTransactionAcceptedResponse::fromArray($data),
            403 => ErrorDetail::fromArray($data),
            default => throw new TalerException('Unexpected response status code: ' . $response->getStatusCode())
        };
    }
}
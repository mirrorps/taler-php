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
        return Actions\Keys::run($this, $headers);
    }

    /**
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function getKeysAsync(array $headers = []): mixed
    {
        return Actions\Keys::runAsync($this, $headers);
    }

    /**
     * @param array<string, string> $headers Optional request headers
     * @return FutureKeysResponse|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public function getManagementKeys(array $headers = []): FutureKeysResponse|array
    {
        return Actions\ManagementKeys::run($this, $headers);
    }

    /**
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function getManagementKeysAsync(array $headers = []): mixed
    {
        return Actions\ManagementKeys::runAsync($this, $headers);
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
        return Actions\Transfer::run($this, $wtid, $headers);
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
        return Actions\Transfer::runAsync($this, $wtid, $headers);
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
        return Actions\Deposits::run(
            $this,
            $H_WIRE,
            $MERCHANT_PUB,
            $H_CONTRACT_TERMS,
            $COIN_PUB,
            $merchant_sig,
            $timeout_ms,
            $lpt,
            $headers
        );
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
        return Actions\Deposits::runAsync(
            $this,
            $H_WIRE,
            $MERCHANT_PUB,
            $H_CONTRACT_TERMS,
            $COIN_PUB,
            $merchant_sig,
            $timeout_ms,
            $lpt,
            $headers
        );
    }
}
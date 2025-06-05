<?php

namespace Taler\Api\Exchange;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Dto\ErrorDetail;
use Taler\Api\Exchange\Dto\TrackTransactionAcceptedResponse;
use Taler\Api\Exchange\Dto\TrackTransactionResponse;
use Taler\Api\Exchange\Dto\TrackTransferResponse;
use Taler\Exception\TalerException;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

class ExchangeClient
{
    public function __construct(
        private Taler $taler,
        private HttpClientWrapper $client
    ) {
    }

    /**
     * @param array<string, string> $headers Optional request headers
     * @return array<string, mixed>|null
     */
    public function getConfig(array $headers = []): ?array
    {
        $response = $this->client->request('GET', 'config', $headers);

        return json_decode((string)$response->getBody(), true);
    }

    /**
     * @param array<string, string> $headers Optional request headers
     * @return array<string, mixed>|null
     */
    public function getKeys(array $headers = []): ?array
    {
        $response = $this->client->request('GET', 'keys', $headers);

        return json_decode((string)$response->getBody(), true);
    }

    /**
     * Retrieve the exchange's management keys
     * 
     * @param array<string, string> $headers Optional request headers
     * @return array<string, mixed>|null
     */
    public function getManagementKeys(array $headers = []): ?array
    {
        $response = $this->client->request('GET', 'management/keys', $headers);

        return json_decode((string)$response->getBody(), true);
    }

    /**
     * Retrieve details about a specific wire transfer
     * 
     * @param string $wtid The wire transfer identifier
     * @param array<string, string> $headers Optional request headers
     * @return TrackTransferResponse|array<string, mixed>
     * 
     * @see https://docs.taler.net/core/api-exchange.html#get--transfers-$WTID
     */
    public function getTransfer(string $wtid, array $headers = []): TrackTransferResponse|array
    {
        $response = $this->client->request('GET', "transfers/{$wtid}", $headers);

        if (!$this->taler->getWrappedResponse()) {
            return json_decode((string)$response->getBody(), true);
        }

        return $this->handleTransferResponse($response);
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
        $response = $this->client->request('GET', "deposits/{$H_WIRE}/{$MERCHANT_PUB}/{$H_CONTRACT_TERMS}/{$COIN_PUB}?merchant_sig={$merchant_sig}&timeout_ms={$timeout_ms}&lpt={$lpt}", $headers);
        
        if (!$this->taler->getWrappedResponse()) {
            return json_decode((string)$response->getBody(), true);
        }

        return $this->handleDepositsResponse($response);
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
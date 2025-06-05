<?php

namespace Taler\Exchange;

use Taler\Http\HttpClientWrapper;

class ExchangeClient
{
    public function __construct(
        private HttpClientWrapper $client
    ) {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getConfig(array $headers = []): ?array
    {
        $response = $this->client->request('GET', 'config', $headers);
        return json_decode((string)$response->getBody(), true) ?? null;
    }

    /**
     * Retrieve the exchange's signing keys
     * 
     * @return array<string, mixed>|null
     */
    public function getKeys(array $headers = []): ?array
    {
        $response = $this->client->request('GET', 'keys', $headers);
        return json_decode((string)$response->getBody(), true) ?? null;
    }

    /**
     * Retrieve the exchange's management keys
     * 
     * @return array<string, mixed>|null
     */
    public function getManagementKeys(array $headers = []): ?array
    {
        $response = $this->client->request('GET', 'management/keys', $headers);
        return json_decode((string)$response->getBody(), true) ?? null;
    }

    /**
     * Retrieve details about a specific wire transfer
     * 
     * @param string $wtid The wire transfer identifier
     * @return array<string, mixed>|null
     */
    public function getTransfer(string $wtid, array $headers = []): ?array
    {
        $response = $this->client->request('GET', "transfers/{$wtid}", $headers);
        return json_decode((string)$response->getBody(), true) ?? null;
    }

    /**
     * @param $H_WIRE string (the hash of the merchant's payment details)
     * @param $MERCHANT_PUB string (the merchant's public key (EdDSA))
     * @param $H_CONTRACT_TERMS string (the hash of the contract terms that were paid)
     * @param $COIN_PUB string (the public key of the coin used for the payment)
     *
     * Query params:
     * @param $merchant_sig string (EdDSA signature of the merchant made with purpose TALER_SIGNATURE_MERCHANT_TRACK_TRANSACTION over a TALER_DepositTrackPS, affirming that it is really the merchant who requires obtaining the wire transfer identifier)
     * @param $timeout_ms int|float [Optional] (If specified, the exchange will wait up to NUMBER milliseconds for completion of a deposit operation before sending the HTTP response)
     * @param $lpt int [Optional] (Specifies what status change we are long-polling for. Use 1 to wait for the a 202 state where kyc_ok is false or a 200 OK response. 2 to wait exclusively for a 200 OK response)
     *
     * @return array<string, mixed>|null
     *
     * https://docs.taler.net/core/api-exchange.html#get--deposits-$H_WIRE-$MERCHANT_PUB-$H_CONTRACT_TERMS-$COIN_PUB
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
    ): ?array
    {
        $response = $this->client->request('GET', "deposits/{$H_WIRE}/{$MERCHANT_PUB}/{$H_CONTRACT_TERMS}/{$COIN_PUB}?merchant_sig={$merchant_sig}&timeout_ms={$timeout_ms}&lpt={$lpt}", $headers);
        return json_decode((string)$response->getBody(), true) ?? null;
    }
}
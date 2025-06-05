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
    public function getConfig(): ?array
    {
        $response = $this->client->request('GET', 'config');
        return json_decode((string)$response->getBody(), true) ?? null;
    }

    /**
     * Retrieve the exchange's signing keys
     * 
     * @return array<string, mixed>|null
     */
    public function getKeys(): ?array
    {
        $response = $this->client->request('GET', 'keys');
        return json_decode((string)$response->getBody(), true) ?? null;
    }

    /**
     * Retrieve the exchange's management keys
     * 
     * @return array<string, mixed>|null
     */
    public function getManagementKeys(): ?array
    {
        $response = $this->client->request('GET', 'management/keys');
        return json_decode((string)$response->getBody(), true) ?? null;
    }

    /**
     * Retrieve details about a specific wire transfer
     * 
     * @param string $wtid The wire transfer identifier
     * @return array<string, mixed>|null
     */
    public function getTransfer(string $wtid): ?array
    {
        $response = $this->client->request('GET', "transfers/{$wtid}");
        return json_decode((string)$response->getBody(), true) ?? null;
    }

    /**
     * Retrieve information about deposits
     * 
     * @return array<string, mixed>|null
     */
    public function getDeposits(
        string $H_WIRE,
        string $MERCHANT_PUB,
        string $H_CONTRACT_TERMS,
        string $COIN_PUB): ?array
    {
        $response = $this->client->request('GET', "deposits/{$H_WIRE}/{$MERCHANT_PUB}/{$H_CONTRACT_TERMS}/{$COIN_PUB}", []);
        return json_decode((string)$response->getBody(), true) ?? null;
    }
}
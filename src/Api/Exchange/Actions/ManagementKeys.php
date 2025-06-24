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

        return $result;
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
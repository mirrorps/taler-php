<?php

namespace Taler\Api\Instance\Dto;

use Taler\Api\Dto\AccountLimit;

/**
 * DTO representing a single KYC status entry for an exchange/wire account
 */
class MerchantAccountKycRedirect
{
    /**
     * @param string $status Summary status of the KYC process
     * @param string $payto_uri Full payto URI of the bank wire account
     * @param string $h_wire Hash of the salted payto URI (since v17)
     * @param string $exchange_url Base URL of the exchange
     * @param int $exchange_http_status HTTP status code returned by the exchange (since v17)
     * @param bool $no_keys True if no /keys were obtained from the exchange
     * @param bool $auth_conflict True if KYC auth transfer is impossible at this exchange
     * @param int|null $exchange_code Optional numeric error code from the exchange (since v17)
     * @param string|null $access_token Optional AccountAccessToken needed to open KYC SPA
     * @param array<int,AccountLimit>|null $limits Optional array of current account limits
     * @param array<int,string>|null $payto_kycauths Optional array of payto URIs for KYC auth transfers (since v17)
     */
    public function __construct(
        public readonly string $status,
        public readonly string $payto_uri,
        public readonly string $h_wire,
        public readonly string $exchange_url,
        public readonly int $exchange_http_status,
        public readonly bool $no_keys,
        public readonly bool $auth_conflict,
        public readonly ?int $exchange_code = null,
        public readonly ?string $access_token = null,
        public readonly ?array $limits = null,
        public readonly ?array $payto_kycauths = null,
    ) {
    }

    /**
     * @param array{
     *   status: string,
     *   payto_uri: string,
     *   h_wire: string,
     *   exchange_url: string,
     *   exchange_http_status: int,
     *   no_keys: bool,
     *   auth_conflict: bool,
     *   exchange_code?: int,
     *   access_token?: string,
     *   limits?: array<int, array{
     *     operation_type: string,
     *     timeframe: array{d_us: int|string},
     *     threshold: string,
     *     soft_limit?: bool
     *   }>,
     *   payto_kycauths?: array<int, string>
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            status: $data['status'],
            payto_uri: $data['payto_uri'],
            h_wire: $data['h_wire'],
            exchange_url: $data['exchange_url'],
            exchange_http_status: $data['exchange_http_status'],
            no_keys: $data['no_keys'],
            auth_conflict: $data['auth_conflict'],
            exchange_code: $data['exchange_code'] ?? null,
            access_token: $data['access_token'] ?? null,
            limits: isset($data['limits']) ? array_map(
                fn(array $limitData) => AccountLimit::createFromArray($limitData),
                $data['limits']
            ) : null,
            payto_kycauths: $data['payto_kycauths'] ?? null
        );
    }
}




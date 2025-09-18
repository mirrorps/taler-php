<?php

namespace Taler\Api\Instance\Dto;

/**
 * Response for KYC status query containing redirects/info per exchange/account
 */
class MerchantAccountKycRedirectsResponse
{
    /**
     * @param array<int,MerchantAccountKycRedirect> $kyc_data Array of KYC status items
     */
    public function __construct(
        public readonly array $kyc_data,
    ) {
    }

    /**
     * @param array{kyc_data: array<int, array{status: string, payto_uri: string, h_wire: string, exchange_url: string, exchange_http_status: int, no_keys: bool, auth_conflict: bool, exchange_code?: int, access_token?: string, limits?: array<int, array{operation_type: string, timeframe: array{d_us: int|string}, threshold: string, soft_limit?: bool}>, payto_kycauths?: array<int, string>}>} $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            kyc_data: array_map(
                fn(array $item) => MerchantAccountKycRedirect::createFromArray($item),
                $data['kyc_data']
            )
        );
    }
}




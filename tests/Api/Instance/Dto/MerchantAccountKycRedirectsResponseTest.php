<?php

namespace Taler\Tests\Api\Instance\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Instance\Dto\MerchantAccountKycRedirectsResponse;
use Taler\Api\Instance\Dto\MerchantAccountKycRedirect;
use Taler\Api\Dto\AccountLimit;

class MerchantAccountKycRedirectsResponseTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = [
            'kyc_data' => [
                [
                    'status' => 'ready',
                    'payto_uri' => 'payto://iban/DE89370400440532013000',
                    'h_wire' => 'abcd',
                    'exchange_url' => 'https://exchange.example.com',
                    'exchange_http_status' => 200,
                    'no_keys' => false,
                    'auth_conflict' => false,
                    'exchange_code' => 0,
                    'access_token' => 'token-xyz',
                    'limits' => [
                        [
                            'operation_type' => 'WITHDRAW',
                            'timeframe' => ['d_us' => 1000000],
                            'threshold' => 'TESTKUDOS:5.0',
                            'soft_limit' => true,
                        ],
                    ],
                    'payto_kycauths' => [
                        'payto://iban/DE123?amount=EUR:1.0&message=KYC',
                    ],
                ],
            ],
        ];

        $resp = MerchantAccountKycRedirectsResponse::createFromArray($data);
        $this->assertCount(1, $resp->kyc_data);
        $item = $resp->kyc_data[0];
        $this->assertInstanceOf(MerchantAccountKycRedirect::class, $item);
        $this->assertSame('ready', $item->status);
        $this->assertSame('token-xyz', $item->access_token);
        $this->assertIsArray($item->limits);
        $this->assertInstanceOf(AccountLimit::class, $item->limits[0]);
        $this->assertSame('TESTKUDOS:5.0', $item->limits[0]->threshold);
        $this->assertIsArray($item->payto_kycauths);
    }
}




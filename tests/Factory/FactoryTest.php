<?php

namespace Taler\Tests\Factory;

use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Taler\Factory\Factory;
use Taler\Taler;

class FactoryTest extends TestCase
{
    private const BASE_URL = 'https://backend.demo.taler.net/instances/sandbox';

    public function testCreateValidMerchantBackend(): void
    {
        $mockClient = new MockClient();
        $psr17 = new Psr17Factory();

        $payload = json_encode([
            'version' => '42:1:0',
            'name' => 'taler-merchant',
            'currency' => 'EUR',
            'currencies' => [
                'EUR' => [
                    'name' => 'Euro',
                    'currency' => 'EUR',
                    'num_fractional_input_digits' => 2,
                    'num_fractional_normal_digits' => 2,
                    'num_fractional_trailing_zero_digits' => 2,
                    'alt_unit_names' => ['0' => '€']
                ]
            ],
            'exchanges' => [],
            'have_self_provisioning' => false,
            'have_donau' => false
        ], JSON_THROW_ON_ERROR);

        $response = $psr17->createResponse(200)
            ->withHeader('Content-Type', 'application/json');
        $response = $response->withBody($psr17->createStream($payload));
        $mockClient->addResponse($response);

        $taler = Factory::create([
            'base_url' => self::BASE_URL,
            'token' => 'Bearer test-token',
            'client' => $mockClient,
        ]);

        $this->assertInstanceOf(Taler::class, $taler);
    }

    public function testCreateThrowsForNonMerchantBackend(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $mockClient = new MockClient();
        $psr17 = new Psr17Factory();

        $payload = json_encode([
            'version' => '42:1:0',
            'name' => 'taler-exchange',
            'currency' => 'EUR',
            'currencies' => [],
            'currency_specification' => [
                'name' => 'Euro',
                'currency' => 'EUR',
                'num_fractional_input_digits' => 2,
                'num_fractional_normal_digits' => 2,
                'num_fractional_trailing_zero_digits' => 2,
                'alt_unit_names' => ['0' => '€']
            ],
            'supported_kyc_requirements' => []
        ], JSON_THROW_ON_ERROR);

        $response = $psr17->createResponse(200)
            ->withHeader('Content-Type', 'application/json');
        $response = $response->withBody($psr17->createStream($payload));
        $mockClient->addResponse($response);

        Factory::create([
            'base_url' => self::BASE_URL,
            'token' => 'Bearer test-token',
            'client' => $mockClient,
        ]);
    }
}



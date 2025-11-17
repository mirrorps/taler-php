<?php

namespace Taler\Tests\Factory;

use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Taler\Factory\Factory;
use Taler\Taler;

class FactoryManagedAuthTest extends TestCase
{
    private const BASE_URL = 'https://backend.demo.taler.net/instances/sandbox';

    public function testCreateWithCredentialsFetchesTokenAndSetsExpiry(): void
    {
        $mockClient = new MockClient();
        $psr17 = new Psr17Factory();

        $now = time();
        $tokenPayload = json_encode([
            'access_token' => 'Bearer token-1',
            'scope' => 'readonly',
            'expiration' => ['t_s' => $now + 60],
            'refreshable' => false,
        ], JSON_THROW_ON_ERROR);
        $tokenResponse = $psr17->createResponse(200)->withHeader('Content-Type', 'application/json');
        $tokenResponse = $tokenResponse->withBody($psr17->createStream($tokenPayload));

        $configPayload = json_encode([
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
        $configResponse = $psr17->createResponse(200)->withHeader('Content-Type', 'application/json');
        $configResponse = $configResponse->withBody($psr17->createStream($configPayload));

        // First POST to obtain token, then GET /config
        $mockClient->addResponse($tokenResponse);
        $mockClient->addResponse($configResponse);

        $taler = Factory::create([
            'base_url' => self::BASE_URL,
            'username' => 'demo',
            'password' => 'secret',
            'instance' => 'merchantA',
            'scope' => 'readonly',
            'client' => $mockClient,
        ]);

        $this->assertInstanceOf(Taler::class, $taler);
        $this->assertSame('Bearer token-1', $taler->getConfig()->getAuthToken());
        $this->assertIsInt($taler->getConfig()->getAuthTokenExpiresAtTs());

        $requests = $mockClient->getRequests();
        $this->assertCount(2, $requests);
        $this->assertSame('POST', $requests[0]->getMethod());
        $this->assertStringStartsWith('Basic ', (string) $requests[0]->getHeaderLine('Authorization'));
        $this->assertSame('GET', $requests[1]->getMethod());
    }

    public function testAutoRefreshBeforeExpiry(): void
    {
        $mockClient = new MockClient();
        $psr17 = new Psr17Factory();

        $now = time();
        // Initial token (far enough in the future so Factory::create() won't refresh during GET /config)
        $token1Payload = json_encode([
            'access_token' => 'Bearer token-1',
            'scope' => 'readonly',
            'expiration' => ['t_s' => $now + 3600],
            'refreshable' => false,
        ], JSON_THROW_ON_ERROR);
        $token1Response = $psr17->createResponse(200)->withHeader('Content-Type', 'application/json');
        $token1Response = $token1Response->withBody($psr17->createStream($token1Payload));

        // Config response
        $configPayload = json_encode([
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
        $configResponse = $psr17->createResponse(200)->withHeader('Content-Type', 'application/json');
        $configResponse = $configResponse->withBody($psr17->createStream($configPayload));

        // Refreshed token
        $token2Payload = json_encode([
            'access_token' => 'Bearer token-2',
            'scope' => 'readonly',
            'expiration' => ['t_s' => $now + 3600],
            'refreshable' => false,
        ], JSON_THROW_ON_ERROR);
        $token2Response = $psr17->createResponse(200)->withHeader('Content-Type', 'application/json');
        $token2Response = $token2Response->withBody($psr17->createStream($token2Payload));

        // Orders response
        $ordersPayload = json_encode(['orders' => []], JSON_THROW_ON_ERROR);
        $ordersResponse = $psr17->createResponse(200)->withHeader('Content-Type', 'application/json');
        $ordersResponse = $ordersResponse->withBody($psr17->createStream($ordersPayload));

        // Sequence: token1, config, token2 (refresh), orders
        $mockClient->addResponse($token1Response);
        $mockClient->addResponse($configResponse);
        $mockClient->addResponse($token2Response);
        $mockClient->addResponse($ordersResponse);

        $taler = Factory::create([
            'base_url' => self::BASE_URL,
            'username' => 'demo',
            'password' => 'secret',
            'instance' => 'merchantA',
            'scope' => 'readonly',
            'client' => $mockClient,
        ]);

        // Force expiry in the past to trigger refresh
        $taler->getConfig()->setAttribute('authTokenExpiresAtTs', time() - 1);

        // Make an auth-required call; this should trigger provider refresh and then GET orders
        $taler->order()->getOrders(['limit' => '1']);

        $this->assertSame('Bearer token-2', $taler->getConfig()->getAuthToken());

        $requests = $mockClient->getRequests();
        $this->assertGreaterThanOrEqual(4, count($requests));
        // Refresh request should be a POST with Basic auth
        $this->assertSame('POST', $requests[2]->getMethod());
        $this->assertStringStartsWith('Basic ', (string) $requests[2]->getHeaderLine('Authorization'));
        // Orders call should include Bearer token-2
        $this->assertSame('GET', $requests[3]->getMethod());
        $this->assertSame('Bearer token-2', (string) $requests[3]->getHeaderLine('Authorization'));
    }

    public function testExplicitTokenSkipsTokenProvider(): void
    {
        $mockClient = new MockClient();
        $psr17 = new Psr17Factory();

        $configPayload = json_encode([
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
        $configResponse = $psr17->createResponse(200)->withHeader('Content-Type', 'application/json');
        $configResponse = $configResponse->withBody($psr17->createStream($configPayload));
        $mockClient->addResponse($configResponse);

        $taler = Factory::create([
            'base_url' => self::BASE_URL,
            'token' => 'Bearer pre-set',
            'username' => 'demo', // Should be ignored because token provided
            'password' => 'secret',
            'instance' => 'merchantA',
            'client' => $mockClient,
        ]);

        $this->assertInstanceOf(Taler::class, $taler);
        $this->assertSame('Bearer pre-set', $taler->getConfig()->getAuthToken());

        $requests = $mockClient->getRequests();
        $this->assertCount(1, $requests);
        $this->assertSame('GET', $requests[0]->getMethod());
        $this->assertSame('Bearer pre-set', (string) $requests[0]->getHeaderLine('Authorization'));
    }
}



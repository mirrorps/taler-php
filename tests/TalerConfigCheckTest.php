<?php

namespace Taler\Tests;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Taler\Config\TalerConfig;
use Taler\Taler;

class TalerConfigCheckTest extends TestCase
{
    private ClientInterface&MockObject $httpClient;
    private LoggerInterface&MockObject $logger;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(ClientInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    /**
     * Helper to build response with given status and JSON body.
     */
    private function makeJsonResponse(int $status, array $data): ResponseInterface
    {
        /** @var ResponseInterface&MockObject $response */
        $response = $this->createMock(ResponseInterface::class);
        /** @var StreamInterface&MockObject $stream */
        $stream = $this->createMock(StreamInterface::class);
        $json = json_encode($data);
        $stream->method('getContents')->willReturn($json);
        $stream->method('__toString')->willReturn($json);
        $response->method('getStatusCode')->willReturn($status);
        $response->method('getBody')->willReturn($stream);
        return $response;
    }

    public function test_config_check_ok(): void
    {
        $configJson = [
            'version' => '21:0:21',
            'name' => 'taler-merchant',
            'currency' => 'KUDOS',
            'currencies' => [
                'KUDOS' => [
                    'name' => 'KUDOS',
                    'currency' => 'KUDOS',
                    'num_fractional_input_digits' => 2,
                    'num_fractional_normal_digits' => 2,
                    'num_fractional_trailing_zero_digits' => 0,
                    'alt_unit_names' => ['0' => 'K']
                ]
            ]
        ];
        $instanceJson = [
            'name' => 'inst1',
            'merchant_pub' => 'pub',
            'address' => [],
            'jurisdiction' => [],
            'use_stefan' => false,
            'default_wire_transfer_delay' => ['d_us' => 0],
            'default_pay_delay' => ['d_us' => 0],
            'auth' => ['method' => 'token']
        ];
        $ordersJson = ['orders' => []];

        $r1 = $this->makeJsonResponse(200, $configJson);
        $r2 = $this->makeJsonResponse(200, $instanceJson);
        $r3 = $this->makeJsonResponse(200, $ordersJson);

        $this->httpClient
            ->method('sendRequest')
            ->with($this->isInstanceOf(RequestInterface::class))
            ->willReturnOnConsecutiveCalls($r1, $r2, $r3);

        $taler = new Taler(new TalerConfig('https://example.com', 'Bearer abc', true), $this->httpClient, $this->logger);

        $report = $taler->configCheck();
        $this->assertTrue($report['ok']);
        $this->assertTrue($report['config']['ok']);
        $this->assertTrue($report['instance']['ok']);
        $this->assertTrue($report['auth']['ok']);
    }

    public function test_config_404(): void
    {
        /** @var ResponseInterface&MockObject $resp */
        $resp = $this->createMock(ResponseInterface::class);
        /** @var StreamInterface&MockObject $stream */
        $stream = $this->createMock(StreamInterface::class);
        $json = json_encode(['error' => 'not found']);
        $stream->method('getContents')->willReturn($json);
        $stream->method('__toString')->willReturn($json);
        $resp->method('getStatusCode')->willReturn(404);
        $resp->method('getBody')->willReturn($stream);

        $this->httpClient
            ->method('sendRequest')
            ->with($this->isInstanceOf(RequestInterface::class))
            ->willReturn($resp);

        $taler = new Taler(new TalerConfig('https://merchant.invalid', '', true), $this->httpClient, $this->logger);

        $report = $taler->configCheck();
        $this->assertFalse($report['ok']);
        $this->assertFalse($report['config']['ok']);
        $this->assertSame(404, $report['config']['status']);
        $this->assertArrayHasKey('exception', $report['config']);
    }

    public function test_instance_404_and_auth_skipped_when_token_empty(): void
    {
        $configJson = [
            'version' => '21:0:21',
            'name' => 'taler-merchant',
            'currency' => 'KUDOS',
            'currencies' => [
                'KUDOS' => [
                    'name' => 'KUDOS',
                    'currency' => 'KUDOS',
                    'num_fractional_input_digits' => 2,
                    'num_fractional_normal_digits' => 2,
                    'num_fractional_trailing_zero_digits' => 0,
                    'alt_unit_names' => ['0' => 'K']
                ]
            ]
        ];

        $r1 = $this->makeJsonResponse(200, $configJson);
        $this->httpClient
            ->method('sendRequest')
            ->with($this->isInstanceOf(RequestInterface::class))
            ->willReturn($r1);

        $taler = new Taler(new TalerConfig('https://example.com', '', true), $this->httpClient, $this->logger);

        $report = $taler->configCheck();
        $this->assertTrue($report['config']['ok']);
        $this->assertTrue($report['ok']); // instance/auth skipped due to empty token
        $this->assertArrayNotHasKey('instance', $report);
        $this->assertArrayNotHasKey('auth', $report);
    }

    public function test_auth_401(): void
    {
        $configJson = [
            'version' => '21:0:21',
            'name' => 'taler-merchant',
            'currency' => 'KUDOS',
            'currencies' => [
                'KUDOS' => [
                    'name' => 'KUDOS',
                    'currency' => 'KUDOS',
                    'num_fractional_input_digits' => 2,
                    'num_fractional_normal_digits' => 2,
                    'num_fractional_trailing_zero_digits' => 0,
                    'alt_unit_names' => ['0' => 'K']
                ]
            ]
        ];
        $instanceJson = [
            'name' => 'inst1',
            'merchant_pub' => 'pub',
            'address' => [],
            'jurisdiction' => [],
            'use_stefan' => false,
            'default_wire_transfer_delay' => ['d_us' => 0],
            'default_pay_delay' => ['d_us' => 0],
            'auth' => ['method' => 'token']
        ];

        $r1 = $this->makeJsonResponse(200, $configJson);
        $r2 = $this->makeJsonResponse(200, $instanceJson);

        /** @var ResponseInterface&MockObject $resp401 */
        $resp401 = $this->createMock(ResponseInterface::class);
        /** @var StreamInterface&MockObject $stream */
        $stream = $this->createMock(StreamInterface::class);
        $json401 = json_encode(['error' => 'unauthorized']);
        $stream->method('getContents')->willReturn($json401);
        $stream->method('__toString')->willReturn($json401);
        $resp401->method('getStatusCode')->willReturn(401);
        $resp401->method('getBody')->willReturn($stream);

        $this->httpClient
            ->method('sendRequest')
            ->with($this->isInstanceOf(RequestInterface::class))
            ->willReturnOnConsecutiveCalls($r1, $r2, $resp401);

        $taler = new Taler(new TalerConfig('https://example.com', 'Bearer bad', true), $this->httpClient, $this->logger);

        $report = $taler->configCheck();
        $this->assertTrue($report['config']['ok']);
        $this->assertTrue($report['instance']['ok']);
        $this->assertFalse($report['auth']['ok']);
        $this->assertSame(401, $report['auth']['status']);
        $this->assertArrayHasKey('exception', $report['auth']);
        $this->assertFalse($report['ok']);
    }
}




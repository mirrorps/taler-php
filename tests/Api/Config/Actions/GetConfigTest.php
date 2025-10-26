<?php

namespace Taler\Tests\Api\Config\Actions;

use GuzzleHttp\Promise\Promise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Taler\Api\Config\Actions\GetConfig;
use Taler\Api\Config\ConfigClient;
use Taler\Api\Config\Dto\MerchantVersionResponse;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

class GetConfigTest extends TestCase
{
    private ConfigClient $client;
    private ResponseInterface&MockObject $response;
    private StreamInterface&MockObject $stream;
    private LoggerInterface&MockObject $logger;
    private Taler&MockObject $taler;
    private HttpClientWrapper&MockObject $httpClientWrapper;

    protected function setUp(): void
    {
        $this->response = $this->createMock(ResponseInterface::class);
        $this->stream = $this->createMock(StreamInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->taler = $this->createMock(Taler::class);

        $this->taler->method('getLogger')->willReturn($this->logger);
        $this->taler->method('getConfig')->willReturn(new TalerConfig('https://example.com', '', true));

        $this->httpClientWrapper = $this->getMockBuilder(HttpClientWrapper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['request', 'requestAsync'])
            ->getMock();

        $this->client = new ConfigClient($this->taler, $this->httpClientWrapper);
    }

    public function testRunSuccess(): void
    {
        $expected = [
            'version' => '42:1:0',
            'name' => 'taler-merchant',
            'implementation' => 'urn:gnu:taler:merchant:v1',
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
            'exchanges' => [
                [
                    'base_url' => 'https://exchange.example.com',
                    'currency' => 'EUR',
                    'master_pub' => 'EXCHANGEPUBKEY'
                ]
            ],
            'have_self_provisioning' => true,
            'have_donau' => false,
            'mandatory_tan_channels' => ['sms', 'email']
        ];

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn(json_encode($expected));
        $this->response->method('getBody')->willReturn($this->stream);

        $headers = ['X-Test' => 'test'];

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('GET', 'config', $headers)
            ->willReturn($this->response);

        $result = GetConfig::run($this->client, $headers);

        $this->assertInstanceOf(MerchantVersionResponse::class, $result);
        $this->assertSame('taler-merchant', $result->name);
        $this->assertSame('EUR', $result->currency);
        $this->assertArrayHasKey('EUR', $result->currencies);
        $this->assertCount(1, $result->exchanges);
    }

    public function testRunWithTalerException(): void
    {
        $this->expectException(TalerException::class);

        $this->httpClientWrapper->method('request')->willThrowException(new TalerException('boom'));
        GetConfig::run($this->client);
    }

    public function testRunAsync(): void
    {
        $expected = [
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
        ];

        $promise = new Promise();

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn(json_encode($expected));
        $this->response->method('getBody')->willReturn($this->stream);

        $this->httpClientWrapper->expects($this->once())
            ->method('requestAsync')
            ->with('GET', 'config', [])
            ->willReturn($promise);

        $result = GetConfig::runAsync($this->client);
        $promise->resolve($this->response);

        $this->assertInstanceOf(MerchantVersionResponse::class, $result->wait());
        $this->assertSame('taler-merchant', $result->wait()->name);
    }
}



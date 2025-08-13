<?php

namespace Taler\Tests\Api\OtpDevices\Actions;

use GuzzleHttp\Promise\Promise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Taler\Api\OtpDevices\Actions\GetOtpDevice;
use Taler\Api\OtpDevices\Dto\GetOtpDeviceRequest;
use Taler\Api\OtpDevices\Dto\OtpDeviceDetails;
use Taler\Api\OtpDevices\OtpDevicesClient;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

class GetOtpDeviceTest extends TestCase
{
    private OtpDevicesClient $client;
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

        $this->client = new OtpDevicesClient($this->taler, $this->httpClientWrapper);
    }

    public function testRunSuccess(): void
    {
        $deviceId = 'device1';
        $payload = json_encode([
            'device_description' => 'Front desk POS',
            'otp_algorithm' => 'TOTP_WITHOUT_PRICE',
            'otp_ctr' => null,
            'otp_timestamp' => 1700000000,
            'otp_code' => '123456',
        ], JSON_THROW_ON_ERROR);

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn($payload);
        $this->response->method('getBody')->willReturn($this->stream);

        $headers = ['X-Test' => 'test'];

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('GET', "private/otp-devices/{$deviceId}", $headers)
            ->willReturn($this->response);

        $result = GetOtpDevice::run($this->client, $deviceId, null, $headers);
        $this->assertInstanceOf(OtpDeviceDetails::class, $result);
        $this->assertSame('Front desk POS', $result->device_description);
        $this->assertSame('TOTP_WITHOUT_PRICE', $result->otp_algorithm);
        $this->assertNull($result->otp_ctr);
        $this->assertSame(1700000000, $result->otp_timestamp);
        $this->assertSame('123456', $result->otp_code);
    }

    public function testRunWithTalerException(): void
    {
        $this->expectException(TalerException::class);
        $this->httpClientWrapper->method('request')->willThrowException(new TalerException('boom'));
        GetOtpDevice::run($this->client, 'device1');
    }

    public function testRunAsync(): void
    {
        $deviceId = 'device1';
        $promise = new Promise();

        $payload = json_encode([
            'device_description' => 'Front desk POS',
            'otp_algorithm' => 1,
            'otp_timestamp' => 1700000001,
        ], JSON_THROW_ON_ERROR);

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn($payload);
        $this->response->method('getBody')->willReturn($this->stream);

        $this->httpClientWrapper->expects($this->once())
            ->method('requestAsync')
            ->with('GET', "private/otp-devices/{$deviceId}?faketime=1700000001&price=EUR%3A1.23", [], $this->anything())
            ->willReturn($promise);

        // with query params
        $req = new GetOtpDeviceRequest(faketime: 1700000001, price: 'EUR:1.23');
        $result = GetOtpDevice::runAsync($this->client, $deviceId, $req);
        $promise->resolve($this->response);

        $resolved = $result->wait();
        $this->assertInstanceOf(OtpDeviceDetails::class, $resolved);
        $this->assertSame(1700000001, $resolved->otp_timestamp);
    }
}




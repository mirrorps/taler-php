<?php

namespace Taler\Tests\Api\OtpDevices\Actions;

use GuzzleHttp\Promise\Promise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Taler\Api\OtpDevices\Actions\UpdateOtpDevice;
use Taler\Api\OtpDevices\Dto\OtpDevicePatchDetails;
use Taler\Api\OtpDevices\OtpDevicesClient;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

class UpdateOtpDeviceTest extends TestCase
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
        $deviceId = 'dev-1';
        $details = new OtpDevicePatchDetails(
            otp_device_description: 'Front desk TOTP',
            otp_key: 'JBSWY3DPEHPK3PXP',
            otp_algorithm: 'TOTP_WITHOUT_PRICE',
            otp_ctr: null
        );

        $this->response->method('getStatusCode')->willReturn(204);
        $this->stream->method('__toString')->willReturn('');
        $this->response->method('getBody')->willReturn($this->stream);

        $headers = ['X-Test' => 'test'];

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('PATCH', "private/otp-devices/{$deviceId}", $headers, $this->anything())
            ->willReturn($this->response);

        UpdateOtpDevice::run($this->client, $deviceId, $details, $headers);
        $this->addToAssertionCount(1);
    }

    public function testRunWithTalerException(): void
    {
        $this->expectException(TalerException::class);

        $deviceId = 'dev-1';
        $details = new OtpDevicePatchDetails(otp_device_description: 'desc');

        $this->httpClientWrapper->method('request')->willThrowException(new TalerException('boom'));
        UpdateOtpDevice::run($this->client, $deviceId, $details);
    }

    public function testRunAsync(): void
    {
        $deviceId = 'dev-1';
        $details = new OtpDevicePatchDetails(
            otp_device_description: 'Front desk TOTP',
            otp_key: 'JBSWY3DPEHPK3PXP',
            otp_algorithm: 'TOTP_WITH_PRICE',
            otp_ctr: 5
        );

        $promise = new Promise();

        $this->response->method('getStatusCode')->willReturn(204);
        $this->stream->method('__toString')->willReturn('');
        $this->response->method('getBody')->willReturn($this->stream);

        $this->httpClientWrapper->expects($this->once())
            ->method('requestAsync')
            ->with('PATCH', "private/otp-devices/{$deviceId}", [], $this->anything())
            ->willReturn($promise);

        $result = UpdateOtpDevice::runAsync($this->client, $deviceId, $details);
        $promise->resolve($this->response);

        $this->assertNull($result->wait());
    }
}




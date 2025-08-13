<?php

namespace Taler\Tests\Api\OtpDevices\Actions;

use GuzzleHttp\Promise\Promise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Taler\Api\OtpDevices\Actions\CreateOtpDevice;
use Taler\Api\OtpDevices\Dto\OtpDeviceAddDetails;
use Taler\Api\OtpDevices\OtpDevicesClient;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

class CreateOtpDeviceTest extends TestCase
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
        $details = new OtpDeviceAddDetails(
            otp_device_id: 'dev-1',
            otp_device_description: 'Office TOTP',
            otp_key: 'JBSWY3DPEHPK3PXP',
            otp_algorithm: 'TOTP_WITHOUT_PRICE'
        );

        $this->response->method('getStatusCode')->willReturn(204);
        $this->stream->method('__toString')->willReturn('');
        $this->response->method('getBody')->willReturn($this->stream);

        $headers = ['X-Test' => 'test'];

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('POST', 'private/otp-devices', $headers, $this->anything())
            ->willReturn($this->response);

        CreateOtpDevice::run($this->client, $details, $headers);
        $this->addToAssertionCount(1);
    }

    public function testRunWithTalerException(): void
    {
        $this->expectException(TalerException::class);

        $details = new OtpDeviceAddDetails(
            otp_device_id: 'dev-1',
            otp_device_description: 'Office TOTP',
            otp_key: 'JBSWY3DPEHPK3PXP',
            otp_algorithm: 'TOTP_WITHOUT_PRICE'
        );

        $this->httpClientWrapper->method('request')->willThrowException(new TalerException('boom'));
        CreateOtpDevice::run($this->client, $details);
    }

    public function testRunAsync(): void
    {
        $details = new OtpDeviceAddDetails(
            otp_device_id: 'dev-1',
            otp_device_description: 'Office TOTP',
            otp_key: 'JBSWY3DPEHPK3PXP',
            otp_algorithm: 'TOTP_WITHOUT_PRICE'
        );

        $promise = new Promise();

        $this->response->method('getStatusCode')->willReturn(204);
        $this->stream->method('__toString')->willReturn('');
        $this->response->method('getBody')->willReturn($this->stream);

        $this->httpClientWrapper->expects($this->once())
            ->method('requestAsync')
            ->with('POST', 'private/otp-devices', [], $this->anything())
            ->willReturn($promise);

        $result = CreateOtpDevice::runAsync($this->client, $details);
        $promise->resolve($this->response);

        $this->assertNull($result->wait());
    }
}



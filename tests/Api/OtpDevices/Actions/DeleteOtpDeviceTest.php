<?php

namespace Taler\Tests\Api\OtpDevices\Actions;

use GuzzleHttp\Promise\Promise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Taler\Api\OtpDevices\Actions\DeleteOtpDevice;
use Taler\Api\OtpDevices\OtpDevicesClient;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

class DeleteOtpDeviceTest extends TestCase
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

        $this->response->method('getStatusCode')->willReturn(204);
        $this->stream->method('__toString')->willReturn('');
        $this->response->method('getBody')->willReturn($this->stream);

        $headers = [];

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('DELETE', "private/otp-devices/{$deviceId}", $headers)
            ->willReturn($this->response);

        DeleteOtpDevice::run($this->client, $deviceId);
        $this->addToAssertionCount(1);
    }

    public function testRunWithTalerException(): void
    {
        $deviceId = 'dev-1';

        $this->httpClientWrapper->method('request')
            ->willThrowException(new TalerException('Test exception'));

        $this->expectException(TalerException::class);
        DeleteOtpDevice::run($this->client, $deviceId);
    }

    public function testRunWithGenericException(): void
    {
        $deviceId = 'dev-1';

        $this->httpClientWrapper->method('request')
            ->willThrowException(new \RuntimeException('Test generic exception'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Taler delete OTP device request failed'));

        $this->expectException(\RuntimeException::class);
        DeleteOtpDevice::run($this->client, $deviceId);
    }

    public function testRunAsync(): void
    {
        $deviceId = 'dev-1';

        $promise = new Promise();

        $this->response->method('getStatusCode')->willReturn(204);
        $this->stream->method('__toString')->willReturn('');
        $this->response->method('getBody')->willReturn($this->stream);

        $headers = [];

        $this->httpClientWrapper->expects($this->once())
            ->method('requestAsync')
            ->with('DELETE', "private/otp-devices/{$deviceId}", $headers)
            ->willReturn($promise);

        $result = DeleteOtpDevice::runAsync($this->client, $deviceId);
        $promise->resolve($this->response);

        $this->assertNull($result->wait());
    }
}



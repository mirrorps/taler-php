<?php

namespace Taler\Tests\Api\OtpDevices\Actions;

use GuzzleHttp\Promise\Promise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Taler\Api\OtpDevices\Dto\OtpDevicesSummaryResponse;
use Taler\Api\OtpDevices\OtpDevicesClient;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

class GetOtpDevicesTest extends TestCase
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
        $payload = json_encode([
            'otp_devices' => [
                [
                    'otp_device_id' => 'device1',
                    'device_description' => 'Front desk POS',
                ],
                [
                    'otp_device_id' => 'device2',
                    'device_description' => 'Side counter POS',
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn($payload);
        $this->response->method('getBody')->willReturn($this->stream);

        $headers = ['X-Test' => 'test'];

        $this->httpClientWrapper->expects($this->once())
            ->method('request')
            ->with('GET', 'private/otp-devices', $headers)
            ->willReturn($this->response);

        $result = $this->client->getOtpDevices($headers);
        $this->assertInstanceOf(OtpDevicesSummaryResponse::class, $result);
        $this->assertCount(2, $result->otp_devices);
        $this->assertSame('device1', $result->otp_devices[0]->otp_device_id);
        $this->assertSame('Front desk POS', $result->otp_devices[0]->device_description);
    }

    public function testRunWithTalerException(): void
    {
        $this->expectException(TalerException::class);

        $this->httpClientWrapper->method('request')->willThrowException(new TalerException('boom'));
        $this->client->getOtpDevices();
    }

    public function testRunAsync(): void
    {
        $promise = new Promise();

        $payload = json_encode([
            'otp_devices' => [
                [
                    'otp_device_id' => 'device1',
                    'device_description' => 'Front desk POS',
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $this->response->method('getStatusCode')->willReturn(200);
        $this->stream->method('__toString')->willReturn($payload);
        $this->response->method('getBody')->willReturn($this->stream);

        $this->httpClientWrapper->expects($this->once())
            ->method('requestAsync')
            ->with('GET', 'private/otp-devices', [])
            ->willReturn($promise);

        $result = $this->client->getOtpDevicesAsync();
        $promise->resolve($this->response);

        $resolved = $result->wait();
        $this->assertInstanceOf(OtpDevicesSummaryResponse::class, $resolved);
        $this->assertCount(1, $resolved->otp_devices);
        $this->assertSame('device1', $resolved->otp_devices[0]->otp_device_id);
    }
}



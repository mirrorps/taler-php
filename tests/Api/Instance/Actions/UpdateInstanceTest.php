<?php

namespace Taler\Tests\Api\Instance\Actions;

use PHPUnit\Framework\TestCase;
use Taler\Api\Instance\Actions\UpdateInstance;
use Taler\Api\Instance\Dto\InstanceReconfigurationMessage;
use Taler\Api\Instance\InstanceClient;
use Taler\Api\Dto\Location;
use Taler\Api\Dto\RelativeTime;
use Taler\Exception\TalerException;
use Psr\Http\Message\ResponseInterface;

class UpdateInstanceTest extends TestCase
{
    private InstanceClient $instanceClient;
    private ResponseInterface $response;
    private InstanceReconfigurationMessage $message;

    protected function setUp(): void
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject&\Taler\Api\Instance\InstanceClient $instanceClient */
        $instanceClient = $this->createMock(InstanceClient::class);
        $this->instanceClient = $instanceClient;

        /** @var \PHPUnit\Framework\MockObject\MockObject&\Psr\Http\Message\ResponseInterface $response */
        $response = $this->createMock(ResponseInterface::class);
        $this->response = $response;

        $this->message = new InstanceReconfigurationMessage(
            name: 'New Name',
            address: new Location(country: 'DE', town: 'Berlin'),
            jurisdiction: new Location(country: 'DE', town: 'Berlin'),
            use_stefan: true,
            default_wire_transfer_delay: new RelativeTime(86400000000),
            default_pay_delay: new RelativeTime(3600000000),
        );
    }

    public function testRunSuccess204(): void
    {
        $this->instanceClient->expects($this->once())
            ->method('setResponse')
            ->with($this->response);

        $this->instanceClient->expects($this->once())
            ->method('handleWrappedResponse')
            ->willReturnCallback(function(callable $handler) {
                $response = $this->response;
                return $handler($response);
            });

        $this->response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(204);

        $this->instanceClient->expects($this->once())
            ->method('parseResponseBody')
            ->with($this->response, 204)
            ->willReturn(null);

        $httpClient = $this->createMock(\Taler\Http\HttpClientWrapper::class);
        $this->instanceClient->expects($this->once())
            ->method('getClient')
            ->willReturn($httpClient);

        $httpClient->expects($this->once())
            ->method('request')
            ->with('PATCH', 'instances/test-instance/private', [], $this->isType('string'))
            ->willReturn($this->response);

        UpdateInstance::run($this->instanceClient, 'test-instance', $this->message);
        $this->assertTrue(true);
    }

    public function testRunAsync(): void
    {
        $promise = $this->createMock(\GuzzleHttp\Promise\PromiseInterface::class);

        $httpClient = $this->createMock(\Taler\Http\HttpClientWrapper::class);
        $this->instanceClient->expects($this->once())
            ->method('getClient')
            ->willReturn($httpClient);

        $httpClient->expects($this->once())
            ->method('requestAsync')
            ->with('PATCH', 'instances/test-instance/private', [], $this->isType('string'))
            ->willReturn($promise);

        $result = UpdateInstance::runAsync($this->instanceClient, 'test-instance', $this->message);
        $this->assertInstanceOf(\GuzzleHttp\Promise\PromiseInterface::class, $result);
    }

    public function testUnexpectedStatusCode(): void
    {
        $this->instanceClient->expects($this->once())
            ->method('setResponse')
            ->with($this->response);

        $this->instanceClient->expects($this->once())
            ->method('handleWrappedResponse')
            ->willReturnCallback(function(callable $handler) {
                $response = $this->response;
                return $handler($response);
            });

        $this->response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(404);

        $this->instanceClient->expects($this->once())
            ->method('parseResponseBody')
            ->with($this->response, 204)
            ->willThrowException(new TalerException('Not found', 404));

        $httpClient = $this->createMock(\Taler\Http\HttpClientWrapper::class);
        $this->instanceClient->expects($this->once())
            ->method('getClient')
            ->willReturn($httpClient);

        $httpClient->expects($this->once())
            ->method('request')
            ->with('PATCH', 'instances/test-instance/private', [], $this->isType('string'))
            ->willReturn($this->response);

        $this->expectException(TalerException::class);
        $this->expectExceptionMessage('Not found');

        UpdateInstance::run($this->instanceClient, 'test-instance', $this->message);
    }

    public function testJsonEncoding(): void
    {
        $this->instanceClient->expects($this->once())
            ->method('setResponse')
            ->with($this->response);

        $this->instanceClient->expects($this->once())
            ->method('handleWrappedResponse')
            ->willReturnCallback(function(callable $handler) {
                $response = $this->response;
                return $handler($response);
            });

        $this->response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(204);

        $this->instanceClient->expects($this->once())
            ->method('parseResponseBody')
            ->with($this->response, 204)
            ->willReturn(null);

        $httpClient = $this->createMock(\Taler\Http\HttpClientWrapper::class);
        $this->instanceClient->expects($this->once())
            ->method('getClient')
            ->willReturn($httpClient);

        $httpClient->expects($this->once())
            ->method('request')
            ->with(
                'PATCH',
                'instances/test-instance/private',
                [],
                $this->callback(function ($jsonString) {
                    $data = json_decode($jsonString, true);
                    return isset($data['name']) && $data['name'] === 'New Name' &&
                           isset($data['address']) && is_array($data['address']) &&
                           isset($data['jurisdiction']) && is_array($data['jurisdiction']) &&
                           isset($data['use_stefan']) && $data['use_stefan'] === true &&
                           isset($data['default_wire_transfer_delay']['d_us']) &&
                           isset($data['default_pay_delay']['d_us']);
                })
            )
            ->willReturn($this->response);

        UpdateInstance::run($this->instanceClient, 'test-instance', $this->message);
        $this->assertTrue(true);
    }
}

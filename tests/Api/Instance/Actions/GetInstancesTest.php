<?php

namespace Taler\Tests\Api\Instance\Actions;

use PHPUnit\Framework\TestCase;
use Taler\Api\Instance\Actions\GetInstances;
use Taler\Api\Instance\InstanceClient;
use Taler\Api\Instance\Dto\InstancesResponse;
use Taler\Exception\TalerException;
use Psr\Http\Message\ResponseInterface;

class GetInstancesTest extends TestCase
{
    private InstanceClient $instanceClient;
    private ResponseInterface $response;

    protected function setUp(): void
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject&\Taler\Api\Instance\InstanceClient $instanceClient */
        $instanceClient = $this->createMock(InstanceClient::class);
        $this->instanceClient = $instanceClient;

        /** @var \PHPUnit\Framework\MockObject\MockObject&\Psr\Http\Message\ResponseInterface $response */
        $response = $this->createMock(ResponseInterface::class);
        $this->response = $response;
    }

    public function testRunSuccess200(): void
    {
        $data = [
            'instances' => [
                [
                    'name' => 'Shop A',
                    'id' => 'shop-a',
                    'merchant_pub' => 'ABCD1234',
                    'payment_targets' => ['default'],
                    'deleted' => false
                ]
            ]
        ];

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
            ->willReturn(200);

        $this->instanceClient->expects($this->once())
            ->method('parseResponseBody')
            ->with($this->response, 200)
            ->willReturn($data);

        $httpClient = $this->createMock(\Taler\Http\HttpClientWrapper::class);
        $this->instanceClient->expects($this->once())
            ->method('getClient')
            ->willReturn($httpClient);

        $httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'management/instances', [],)
            ->willReturn($this->response);

        $result = GetInstances::run($this->instanceClient);
        $this->assertInstanceOf(InstancesResponse::class, $result);
        $this->assertCount(1, $result->instances);
        $this->assertSame('shop-a', $result->instances[0]->id);
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
            ->with('GET', 'management/instances', [],)
            ->willReturn($promise);

        $result = GetInstances::runAsync($this->instanceClient);
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
            ->willReturn(500);

        $this->instanceClient->expects($this->once())
            ->method('parseResponseBody')
            ->with($this->response, 200)
            ->willThrowException(new TalerException('Server error', 500));

        $httpClient = $this->createMock(\Taler\Http\HttpClientWrapper::class);
        $this->instanceClient->expects($this->once())
            ->method('getClient')
            ->willReturn($httpClient);

        $httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'management/instances', [],)
            ->willReturn($this->response);

        $this->expectException(TalerException::class);
        $this->expectExceptionMessage('Server error');

        GetInstances::run($this->instanceClient);
    }
}



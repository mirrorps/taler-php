<?php

namespace Taler\Tests\Api\Instance\Actions;

use PHPUnit\Framework\TestCase;
use Taler\Api\Instance\Actions\GetInstance;
use Taler\Api\Instance\InstanceClient;
use Taler\Api\Instance\Dto\QueryInstancesResponse;
use Taler\Exception\TalerException;
use Psr\Http\Message\ResponseInterface;

class GetInstanceTest extends TestCase
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
            'name' => 'Shop A',
            'merchant_pub' => 'ABCD1234',
            'address' => ['country' => 'DE', 'town' => 'Berlin'],
            'jurisdiction' => ['country' => 'DE', 'town' => 'Berlin'],
            'use_stefan' => true,
            'default_wire_transfer_delay' => ['d_us' => 86400000000],
            'default_pay_delay' => ['d_us' => 3600000000],
            'auth' => ['method' => 'token'],
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
            ->with('GET', 'instances/test-instance/private', [],)
            ->willReturn($this->response);

        $result = GetInstance::run($this->instanceClient, 'test-instance');
        $this->assertInstanceOf(QueryInstancesResponse::class, $result);
        $this->assertSame('Shop A', $result->name);
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
            ->with('GET', 'instances/test-instance/private', [],)
            ->willReturn($promise);

        $result = GetInstance::runAsync($this->instanceClient, 'test-instance');
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
            ->with('GET', 'instances/test-instance/private', [],)
            ->willReturn($this->response);

        $this->expectException(TalerException::class);
        $this->expectExceptionMessage('Server error');

        GetInstance::run($this->instanceClient, 'test-instance');
    }
}



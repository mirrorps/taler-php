<?php

namespace Taler\Tests\Api\Instance\Actions;

use PHPUnit\Framework\TestCase;
use Taler\Api\Instance\Actions\DeleteAccessToken;
use Taler\Api\Instance\InstanceClient;
use Taler\Exception\TalerException;
use Psr\Http\Message\ResponseInterface;

class DeleteAccessTokenTest extends TestCase
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
            ->with('DELETE', 'instances/test-instance/private/token', [],)
            ->willReturn($this->response);

        DeleteAccessToken::run($this->instanceClient, 'test-instance');
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
            ->with('DELETE', 'instances/test-instance/private/token', [],)
            ->willReturn($promise);

        $result = DeleteAccessToken::runAsync($this->instanceClient, 'test-instance');
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
            ->with($this->response, 204)
            ->willThrowException(new TalerException('Server error', 500));

        $httpClient = $this->createMock(\Taler\Http\HttpClientWrapper::class);
        $this->instanceClient->expects($this->once())
            ->method('getClient')
            ->willReturn($httpClient);

        $httpClient->expects($this->once())
            ->method('request')
            ->with('DELETE', 'instances/test-instance/private/token', [],)
            ->willReturn($this->response);

        $this->expectException(TalerException::class);
        $this->expectExceptionMessage('Server error');

        DeleteAccessToken::run($this->instanceClient, 'test-instance');
    }
}



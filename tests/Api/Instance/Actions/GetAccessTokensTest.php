<?php

namespace Taler\Tests\Api\Instance\Actions;

use PHPUnit\Framework\TestCase;
use Taler\Api\Instance\Actions\GetAccessTokens;
use Taler\Api\Instance\InstanceClient;
use Taler\Api\Instance\Dto\GetAccessTokensRequest;
use Taler\Api\Instance\Dto\TokenInfos;
use Taler\Exception\TalerException;
use Psr\Http\Message\ResponseInterface;

class GetAccessTokensTest extends TestCase
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
            'tokens' => [
                [
                    'creation_time' => ['t_s' => 1],
                    'expiration' => ['t_s' => 2],
                    'scope' => 'readonly',
                    'refreshable' => false,
                    'serial' => 1,
                ],
            ],
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
            ->with('GET', 'instances/test-instance/private/tokens?limit=-20&offset=10', [],)
            ->willReturn($this->response);

        $req = new GetAccessTokensRequest(limit: -20, offset: 10);
        $result = GetAccessTokens::run($this->instanceClient, 'test-instance', $req);
        $this->assertInstanceOf(TokenInfos::class, $result);
        $this->assertCount(1, $result->tokens);
    }

    public function testRunNoContent204(): void
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

        $httpClient = $this->createMock(\Taler\Http\HttpClientWrapper::class);
        $this->instanceClient->expects($this->once())
            ->method('getClient')
            ->willReturn($httpClient);

        $httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'instances/test-instance/private/tokens?', [],)
            ->willReturn($this->response);

        $result = GetAccessTokens::run($this->instanceClient, 'test-instance');
        $this->assertNull($result);
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
            ->with('GET', 'instances/test-instance/private/tokens?limit=-5', [],)
            ->willReturn($promise);

        $req = new GetAccessTokensRequest(limit: -5);
        $result = GetAccessTokens::runAsync($this->instanceClient, 'test-instance', $req);
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
            ->with('GET', 'instances/test-instance/private/tokens?', [],)
            ->willReturn($this->response);

        $this->expectException(TalerException::class);
        $this->expectExceptionMessage('Server error');

        GetAccessTokens::run($this->instanceClient, 'test-instance');
    }
}



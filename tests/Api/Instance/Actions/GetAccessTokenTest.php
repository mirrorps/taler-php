<?php

namespace Taler\Tests\Api\Instance\Actions;

use PHPUnit\Framework\TestCase;
use Taler\Api\Instance\Actions\GetAccessToken;
use Taler\Api\Instance\InstanceClient;
use Taler\Api\Instance\Dto\LoginTokenRequest;
use Taler\Api\Instance\Dto\LoginTokenSuccessResponse;
use Taler\Exception\TalerException;
use Psr\Http\Message\ResponseInterface;

class GetAccessTokenTest extends TestCase
{
    private InstanceClient $instanceClient;
    private LoginTokenRequest $requestDto;
    private ResponseInterface $response;

    protected function setUp(): void
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject&\Taler\Api\Instance\InstanceClient $instanceClient */
        $instanceClient = $this->createMock(InstanceClient::class);
        $this->instanceClient = $instanceClient;

        $this->requestDto = new LoginTokenRequest('readonly');

        /** @var \PHPUnit\Framework\MockObject\MockObject&\Psr\Http\Message\ResponseInterface $response */
        $response = $this->createMock(ResponseInterface::class);
        $this->response = $response;
    }

    public function testRunSuccess(): void
    {
        $data = [
            'access_token' => 'Bearer xyz',
            'scope' => 'readonly',
            'expiration' => ['t_s' => 1700000000],
            'refreshable' => false,
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
            ->with('POST', 'instances/test-instance/private/token', [], $this->isType('string'))
            ->willReturn($this->response);

        $result = GetAccessToken::run($this->instanceClient, 'test-instance', $this->requestDto);
        $this->assertInstanceOf(LoginTokenSuccessResponse::class, $result);
        $this->assertSame('Bearer xyz', $result->access_token);
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
            ->with('POST', 'instances/test-instance/private/token', [], $this->isType('string'))
            ->willReturn($promise);

        $result = GetAccessToken::runAsync($this->instanceClient, 'test-instance', $this->requestDto);
        $this->assertInstanceOf(\GuzzleHttp\Promise\PromiseInterface::class, $result);
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
            ->willReturn(200);

        $this->instanceClient->expects($this->once())
            ->method('parseResponseBody')
            ->with($this->response, 200)
            ->willReturn([
                'access_token' => 'Bearer A',
                'scope' => 'readonly',
                'expiration' => ['t_s' => 1],
                'refreshable' => false,
            ]);

        $httpClient = $this->createMock(\Taler\Http\HttpClientWrapper::class);
        $this->instanceClient->expects($this->once())
            ->method('getClient')
            ->willReturn($httpClient);

        $httpClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'instances/test-instance/private/token',
                [],
                $this->callback(function ($jsonString) {
                    $data = json_decode($jsonString, true);
                    return isset($data['scope']) && $data['scope'] === 'readonly';
                })
            )
            ->willReturn($this->response);

        GetAccessToken::run($this->instanceClient, 'test-instance', $this->requestDto);
        $this->assertTrue(true);
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
            ->with('POST', 'instances/test-instance/private/token', [], $this->isType('string'))
            ->willReturn($this->response);

        $this->expectException(TalerException::class);
        $this->expectExceptionMessage('Server error');

        GetAccessToken::run($this->instanceClient, 'test-instance', $this->requestDto);
    }
}



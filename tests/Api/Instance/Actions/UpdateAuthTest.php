<?php

namespace Taler\Tests\Api\Instance\Actions;

use PHPUnit\Framework\TestCase;
use Taler\Api\Instance\Actions\UpdateAuth;
use Taler\Api\Instance\InstanceClient;
use Taler\Api\Instance\Dto\InstanceAuthConfigToken;
use Taler\Api\Instance\Dto\Challenge;
use Taler\Exception\TalerException;
use Psr\Http\Message\ResponseInterface;

/**
 * Test cases for UpdateAuth action.
 *
 * @since v21
 */
class UpdateAuthTest extends TestCase
{
    private InstanceClient $instanceClient;
    private InstanceAuthConfigToken $authConfig;
    private ResponseInterface $response;

    protected function setUp(): void
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject&\Taler\Api\Instance\InstanceClient $instanceClient */
        $instanceClient = $this->createMock(InstanceClient::class);
        $this->instanceClient = $instanceClient;

        $this->authConfig = new InstanceAuthConfigToken('new-password');

        /** @var \PHPUnit\Framework\MockObject\MockObject&\Psr\Http\Message\ResponseInterface $response */
        $response = $this->createMock(ResponseInterface::class);
        $this->response = $response;
    }

    /**
     * Test successful auth update (204 response).
     */
    public function testSuccessfulAuthUpdate(): void
    {
        $this->response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(204);

        $this->instanceClient->expects($this->once())
            ->method('setResponse')
            ->with($this->response);

        $this->instanceClient->expects($this->once())
            ->method('getResponse')
            ->willReturn($this->response);

        $httpClient = $this->createMock(\Taler\Http\HttpClientWrapper::class);
        $this->instanceClient->expects($this->once())
            ->method('getClient')
            ->willReturn($httpClient);

        $httpClient->expects($this->once())
            ->method('request')
            ->with('POST', 'instances/test-instance/private/auth', [], $this->isType('string'))
            ->willReturn($this->response);

        $result = UpdateAuth::run($this->instanceClient, 'test-instance', $this->authConfig);

        $this->assertNull($result);
    }

    /**
     * Test 2FA challenge response (202).
     */
    public function testTwoFactorChallengeResponse(): void
    {
        $challengeData = [
            'challenge_id' => 'challenge-456'
        ];

        $this->response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(202);

        $this->instanceClient->expects($this->once())
            ->method('setResponse')
            ->with($this->response);

        $this->instanceClient->expects($this->once())
            ->method('getResponse')
            ->willReturn($this->response);

        $this->instanceClient->expects($this->once())
            ->method('parseResponseBody')
            ->with($this->response, 202)
            ->willReturn($challengeData);

        $httpClient = $this->createMock(\Taler\Http\HttpClientWrapper::class);
        $this->instanceClient->expects($this->once())
            ->method('getClient')
            ->willReturn($httpClient);

        $httpClient->expects($this->once())
            ->method('request')
            ->with('POST', 'instances/test-instance/private/auth', [], $this->isType('string'))
            ->willReturn($this->response);

        $result = UpdateAuth::run($this->instanceClient, 'test-instance', $this->authConfig);

        $this->assertInstanceOf(Challenge::class, $result);
        $this->assertEquals('challenge-456', $result->getChallengeId());
    }

    /**
     * Test unexpected status code handling via parseResponseBody (e.g., 404 or 500).
     */
    public function testUnexpectedStatusCode(): void
    {
        $this->response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(500);

        $this->instanceClient->expects($this->once())
            ->method('setResponse')
            ->with($this->response);

        $this->instanceClient->expects($this->once())
            ->method('getResponse')
            ->willReturn($this->response);

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
            ->with('POST', 'instances/test-instance/private/auth', [], $this->isType('string'))
            ->willReturn($this->response);

        $this->expectException(TalerException::class);
        $this->expectExceptionMessage('Server error');

        UpdateAuth::run($this->instanceClient, 'test-instance', $this->authConfig);
    }

    /**
     * Test runAsync method structure.
     */
    public function testRunAsyncMethod(): void
    {
        $promise = $this->createMock(\GuzzleHttp\Promise\PromiseInterface::class);

        $httpClient = $this->createMock(\Taler\Http\HttpClientWrapper::class);
        $this->instanceClient->expects($this->once())
            ->method('getClient')
            ->willReturn($httpClient);

        $httpClient->expects($this->once())
            ->method('requestAsync')
            ->with('POST', 'instances/test-instance/private/auth', [], $this->isType('string'))
            ->willReturn($promise);

        $result = UpdateAuth::runAsync($this->instanceClient, 'test-instance', $this->authConfig);
        $this->assertInstanceOf(\GuzzleHttp\Promise\PromiseInterface::class, $result);
    }

    /**
     * Test that JSON encoding includes the auth configuration.
     */
    public function testJsonEncoding(): void
    {
        $this->response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(204);

        $this->instanceClient->expects($this->once())
            ->method('setResponse')
            ->with($this->response);

        $this->instanceClient->expects($this->once())
            ->method('getResponse')
            ->willReturn($this->response);

        $httpClient = $this->createMock(\Taler\Http\HttpClientWrapper::class);
        $this->instanceClient->expects($this->once())
            ->method('getClient')
            ->willReturn($httpClient);

        $httpClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'instances/test-instance/private/auth',
                [],
                $this->callback(function($jsonString) {
                    $data = json_decode($jsonString, true);
                    return isset($data['method']) && $data['method'] === 'token' &&
                           isset($data['password']) && $data['password'] === 'new-password';
                })
            )
            ->willReturn($this->response);

        UpdateAuth::run($this->instanceClient, 'test-instance', $this->authConfig);

        $this->assertTrue(true);
    }
}



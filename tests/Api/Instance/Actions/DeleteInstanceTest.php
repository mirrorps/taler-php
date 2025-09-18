<?php

namespace Taler\Tests\Api\Instance\Actions;

use PHPUnit\Framework\TestCase;
use Taler\Api\Instance\Actions\DeleteInstance;
use Taler\Api\Instance\Dto\Challenge;
use Taler\Api\Instance\InstanceClient;
use Taler\Exception\TalerException;
use Psr\Http\Message\ResponseInterface;

class DeleteInstanceTest extends TestCase
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

    public function testRunSuccess204Disable(): void
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
            ->with('DELETE', 'instances/test-instance/private', [],)
            ->willReturn($this->response);

        $result = DeleteInstance::run($this->instanceClient, 'test-instance');
        $this->assertNull($result);
    }

    public function testRunSuccess202ChallengeOnPurge(): void
    {
        $challengeData = [
            'challenge_id' => 'challenge-789',
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
            ->with('DELETE', 'instances/test-instance/private?purge=YES', [],)
            ->willReturn($this->response);

        $result = DeleteInstance::run($this->instanceClient, 'test-instance', true);
        $this->assertInstanceOf(Challenge::class, $result);
        $this->assertEquals('challenge-789', $result->getChallengeId());
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
            ->with('DELETE', 'instances/test-instance/private?purge=YES', [],)
            ->willReturn($promise);

        $result = DeleteInstance::runAsync($this->instanceClient, 'test-instance', true);
        $this->assertInstanceOf(\GuzzleHttp\Promise\PromiseInterface::class, $result);
    }

    public function testUnexpectedStatusCode(): void
    {
        $this->response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(409);

        $this->instanceClient->expects($this->once())
            ->method('setResponse')
            ->with($this->response);

        $this->instanceClient->expects($this->once())
            ->method('getResponse')
            ->willReturn($this->response);

        $this->instanceClient->expects($this->once())
            ->method('parseResponseBody')
            ->with($this->response, 204)
            ->willThrowException(new TalerException('Conflict', 409));

        $httpClient = $this->createMock(\Taler\Http\HttpClientWrapper::class);
        $this->instanceClient->expects($this->once())
            ->method('getClient')
            ->willReturn($httpClient);

        $httpClient->expects($this->once())
            ->method('request')
            ->with('DELETE', 'instances/test-instance/private', [],)
            ->willReturn($this->response);

        $this->expectException(TalerException::class);
        $this->expectExceptionMessage('Conflict');

        DeleteInstance::run($this->instanceClient, 'test-instance');
    }
}



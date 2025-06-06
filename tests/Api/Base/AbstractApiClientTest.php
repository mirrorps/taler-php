<?php

namespace Taler\Tests\Api\Base;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Taler\Api\Base\AbstractApiClient;
use Taler\Api\Base\BaseApiClient;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;
use Taler\Config;

class AbstractApiClientTest extends TestCase
{
    /** @var Taler&MockObject */
    private $taler;

    /** @var Config\TalerConfig&MockObject */
    private $config;

    /** @var HttpClientWrapper&MockObject */
    private $clientMock;

    /** @var ResponseInterface&MockObject */
    private $responseMock;

    /** @var StreamInterface&MockObject */
    private $streamMock;

    protected function setUp(): void
    {
        $this->config = $this->createMock(Config\TalerConfig::class);
        $this->taler = $this->createMock(Taler::class);
        $this->taler->method('getConfig')->willReturn($this->config);
        $this->clientMock = $this->createMock(HttpClientWrapper::class);
        $this->responseMock = $this->createMock(ResponseInterface::class);
        $this->streamMock = $this->createMock(StreamInterface::class);
    }

    public function testHandleWrappedResponseWithWrapTrue(): void
    {
        $this->config->method('getWrapResponse')->willReturn(true);
        $handler = function(ResponseInterface $response) {
            return ['handler' => $response];
        };

        $testClient = $this->getTestClient();
        $testClient->setResponse($this->responseMock);

        $result = $testClient->handleWrappedResponse($handler);
        $this->assertEquals(['handler' => $this->responseMock], $result);
    }

    public function testHandleWrappedResponseWithWrapFalse(): void
    {
        $this->config->method('getWrapResponse')->willReturn(false);
        $handler = function(ResponseInterface $response) {
            return ['handler' => $response];
        };

        $testClient = $this->getTestClient();
        $testClient->setResponse($this->responseMock);

        // Mock the response body with proper StreamInterface
        $this->streamMock->method('__toString')
            ->willReturn('{"key": "value"}');
        
        $this->responseMock->method('getBody')
            ->willReturn($this->streamMock);

        $result = $testClient->handleWrappedResponse($handler);
        $this->assertEquals(['key' => 'value'], $result);
    }

    public function testHandleWrappedResponseInvalidJson(): void
    {
        $this->config->method('getWrapResponse')->willReturn(false);
        $handler = function(ResponseInterface $response) {
            return ['handler' => $response];
        };

        $testClient = $this->getTestClient();
        $testClient->setResponse($this->responseMock);

        // Mock invalid JSON response with proper StreamInterface
        $this->streamMock->method('__toString')
            ->willReturn('invalid json');
        
        $this->responseMock->method('getBody')
            ->willReturn($this->streamMock);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Invalid JSON response:/');
        $testClient->handleWrappedResponse($handler);
    }

    private function getTestClient(): AbstractApiClient
    {
        return new class($this->taler, $this->clientMock) extends AbstractApiClient {};
    }
}
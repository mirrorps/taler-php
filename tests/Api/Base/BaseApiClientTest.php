<?php

namespace Taler\Tests\Api\Base;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use Taler\Api\Base\BaseApiClient;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

class BaseApiClientTest extends TestCase
{
    /** @var Taler&MockObject */
    private $talerMock;

    /** @var HttpClientWrapper&MockObject */
    private $clientMock;

    /** @var ResponseInterface&MockObject */
    private $responseMock;

    private BaseApiClient $baseApiClient;

    protected function setUp(): void
    {
        $this->talerMock = $this->createMock(Taler::class);
        $this->clientMock = $this->createMock(HttpClientWrapper::class);
        $this->responseMock = $this->createMock(ResponseInterface::class);

        $this->baseApiClient = new BaseApiClient($this->talerMock, $this->clientMock);
    }

    public function testConstructorAndGetters(): void
    {
        $this->assertSame($this->talerMock, $this->baseApiClient->getTaler());
        $this->assertSame($this->clientMock, $this->baseApiClient->getClient());
    }

    public function testSetAndGetResponse(): void
    {
        $this->baseApiClient->setResponse($this->responseMock);
        $this->assertSame($this->responseMock, $this->baseApiClient->getResponse());
    }

    public function testSetAndGetClient(): void
    {
        /** @var HttpClientWrapper&MockObject */
        $newClientMock = $this->createMock(HttpClientWrapper::class);
        $this->baseApiClient->setClient($newClientMock);
        $this->assertSame($newClientMock, $this->baseApiClient->getClient());
    }
}
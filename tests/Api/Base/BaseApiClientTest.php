<?php

namespace Taler\Tests\Api\Base;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Taler\Api\Base\BaseApiClient;
use Taler\Http\HttpClientWrapper;
use Taler\Taler;

class BaseApiClientTest extends TestCase
{
    private $talerMock;
    private $clientMock;
    private $responseMock;
    private $baseApiClient;

    protected function setUp(): void
    {
        $this->talerMock = $this->createMock(Taler::class);
        $this->clientMock = $this->createMock(HttpClientWrapper::class);
        $this->responseMock = $this->createMock(ResponseInterface::class);

        $this->baseApiClient = new BaseApiClient($this->talerMock, $this->clientMock);
    }

    public function testConstructorAndGetters()
    {
        $this->assertSame($this->talerMock, $this->baseApiClient->getTaler());
        $this->assertSame($this->clientMock, $this->baseApiClient->getClient());
    }

    public function testSetAndGetResponse()
    {
        $this->baseApiClient->setResponse($this->responseMock);
        $this->assertSame($this->responseMock, $this->baseApiClient->getResponse());
    }

    public function testSetAndGetClient()
    {
        $newClientMock = $this->createMock(HttpClientWrapper::class);
        $this->baseApiClient->setClient($newClientMock);
        $this->assertSame($newClientMock, $this->baseApiClient->getClient());
    }
}
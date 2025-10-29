<?php

namespace Taler\Tests\Api\DonauCharity\Actions;

use PHPUnit\Framework\TestCase;
use Taler\Api\DonauCharity\Actions\DeleteDonauCharityBySerial;
use Taler\Api\DonauCharity\DonauCharityClient;
use Taler\Exception\TalerException;
use Psr\Http\Message\ResponseInterface;

class DeleteDonauCharityBySerialTest extends TestCase
{
    private DonauCharityClient $client;
    private ResponseInterface $response;

    protected function setUp(): void
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject&\Taler\Api\DonauCharity\DonauCharityClient $client */
        $client = $this->createMock(DonauCharityClient::class);
        $this->client = $client;

        /** @var \PHPUnit\Framework\MockObject\MockObject&\Psr\Http\Message\ResponseInterface $response */
        $response = $this->createMock(ResponseInterface::class);
        $this->response = $response;
    }

    public function testRunSuccess204(): void
    {
        $this->client->expects($this->once())
            ->method('setResponse')
            ->with($this->response);

        $this->client->expects($this->once())
            ->method('handleWrappedResponse')
            ->willReturnCallback(function(callable $handler) {
                $response = $this->response;
                return $handler($response);
            });

        $this->response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(204);

        $this->client->expects($this->once())
            ->method('parseResponseBody')
            ->with($this->response, 204)
            ->willReturn(null);

        $httpClient = $this->createMock(\Taler\Http\HttpClientWrapper::class);
        $this->client->expects($this->once())
            ->method('getClient')
            ->willReturn($httpClient);

        $httpClient->expects($this->once())
            ->method('request')
            ->with('DELETE', 'private/donau/321', [],)
            ->willReturn($this->response);

        DeleteDonauCharityBySerial::run($this->client, 321);
        $this->assertTrue(true);
    }

    public function testRunAsync(): void
    {
        $promise = $this->createMock(\GuzzleHttp\Promise\PromiseInterface::class);

        $httpClient = $this->createMock(\Taler\Http\HttpClientWrapper::class);
        $this->client->expects($this->once())
            ->method('getClient')
            ->willReturn($httpClient);

        $httpClient->expects($this->once())
            ->method('requestAsync')
            ->with('DELETE', 'private/donau/321', [],)
            ->willReturn($promise);

        $result = DeleteDonauCharityBySerial::runAsync($this->client, 321);
        $this->assertInstanceOf(\GuzzleHttp\Promise\PromiseInterface::class, $result);
    }

    public function testUnexpectedStatusCode(): void
    {
        $this->client->expects($this->once())
            ->method('setResponse')
            ->with($this->response);

        $this->client->expects($this->once())
            ->method('handleWrappedResponse')
            ->willReturnCallback(function(callable $handler) {
                $response = $this->response;
                return $handler($response);
            });

        $this->response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(404);

        $this->client->expects($this->once())
            ->method('parseResponseBody')
            ->with($this->response, 204)
            ->willThrowException(new TalerException('No such charity link exists.', 404));

        $httpClient = $this->createMock(\Taler\Http\HttpClientWrapper::class);
        $this->client->expects($this->once())
            ->method('getClient')
            ->willReturn($httpClient);

        $httpClient->expects($this->once())
            ->method('request')
            ->with('DELETE', 'private/donau/321', [],)
            ->willReturn($this->response);

        $this->expectException(TalerException::class);
        $this->expectExceptionMessage('No such charity link exists.');

        DeleteDonauCharityBySerial::run($this->client, 321);
    }
}




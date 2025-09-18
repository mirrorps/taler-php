<?php

namespace Taler\Tests\Api\Instance\Actions;

use PHPUnit\Framework\TestCase;
use Taler\Api\Instance\Actions\GetKycStatus;
use Taler\Api\Instance\InstanceClient;
use Taler\Api\Instance\Dto\GetKycStatusRequest;
use Taler\Api\Instance\Dto\MerchantAccountKycRedirectsResponse;
use Taler\Exception\TalerException;
use Psr\Http\Message\ResponseInterface;

class GetKycStatusTest extends TestCase
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
            'kyc_data' => [
                [
                    'status' => 'ready',
                    'payto_uri' => 'payto://iban/DE1',
                    'h_wire' => 'abcd',
                    'exchange_url' => 'https://ex.example.com',
                    'exchange_http_status' => 200,
                    'no_keys' => false,
                    'auth_conflict' => false,
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
            ->with('GET', 'instances/test-instance/private/kyc?h_wire=HWIRE&lpt=3', [],)
            ->willReturn($this->response);

        $req = new GetKycStatusRequest(h_wire: 'HWIRE', lpt: 3);
        $result = GetKycStatus::run($this->instanceClient, 'test-instance', $req);
        $this->assertInstanceOf(MerchantAccountKycRedirectsResponse::class, $result);
        $this->assertCount(1, $result->kyc_data);
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
            ->with('GET', 'instances/test-instance/private/kyc?', [],)
            ->willReturn($this->response);

        $result = GetKycStatus::run($this->instanceClient, 'test-instance');
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
            ->with('GET', 'instances/test-instance/private/kyc?exchange_url=https%3A%2F%2Fex.example.com', [],)
            ->willReturn($promise);

        $req = new GetKycStatusRequest(exchange_url: 'https://ex.example.com');
        $result = GetKycStatus::runAsync($this->instanceClient, 'test-instance', $req);
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
            ->with('GET', 'instances/test-instance/private/kyc?', [],)
            ->willReturn($this->response);

        $this->expectException(TalerException::class);
        $this->expectExceptionMessage('Server error');

        GetKycStatus::run($this->instanceClient, 'test-instance');
    }
}




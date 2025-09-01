<?php

namespace Taler\Tests\Api\Instance\Actions;

use PHPUnit\Framework\TestCase;
use Taler\Api\Instance\Actions\CreateInstance;
use Taler\Api\Instance\InstanceClient;
use Taler\Api\Instance\Dto\InstanceConfigurationMessage;
use Taler\Api\Dto\Location;
use Taler\Api\Dto\RelativeTime;
use Taler\Api\Instance\Dto\InstanceAuthConfigToken;
use Taler\Exception\TalerException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;



/**
 * Test cases for CreateInstance action.
 */
class CreateInstanceTest extends TestCase
{
    private InstanceClient $instanceClient;
    private InstanceConfigurationMessage $config;
    private ResponseInterface $response;

    protected function setUp(): void
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject&\Taler\Api\Instance\InstanceClient $instanceClient */
        $instanceClient = $this->createMock(InstanceClient::class);
        $this->instanceClient = $instanceClient;

        $auth = new InstanceAuthConfigToken('test-password');
        $address = new Location(country: 'DE', town: 'Berlin');
        $jurisdiction = new Location(country: 'DE', town: 'Berlin');
        $wireTransferDelay = new RelativeTime(d_us: 86400000000);
        $payDelay = new RelativeTime(d_us: 3600000000);

        $this->config = new InstanceConfigurationMessage(
            id: 'test-instance',
            name: 'Test Instance',
            email: 'test@example.com',
            phone_number: '+49123456789',
            website: 'https://example.com',
            logo: 'https://example.com/logo.png',
            auth: $auth,
            address: $address,
            jurisdiction: $jurisdiction,
            use_stefan: true,
            default_wire_transfer_delay: $wireTransferDelay,
            default_pay_delay: $payDelay
        );

        /** @var \PHPUnit\Framework\MockObject\MockObject&\Psr\Http\Message\ResponseInterface $response */
        $response = $this->createMock(ResponseInterface::class);
        $this->response = $response;
    }

    /**
     * Test successful instance creation (204 response).
     */
    public function testSuccessfulInstanceCreation(): void
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

        // We need to mock the HTTP client and its request method
        $httpClient = $this->createMock(\Taler\Http\HttpClientWrapper::class);
        $this->instanceClient->expects($this->once())
            ->method('getClient')
            ->willReturn($httpClient);

        $httpClient->expects($this->once())
            ->method('request')
            ->with('POST', 'instances', [], $this->isType('string'))
            ->willReturn($this->response);

        // This should not throw an exception
        CreateInstance::run($this->instanceClient, $this->config);
    }

    /**
     * Test conflict response (409).
     */
    public function testConflictResponse(): void
    {
        $this->response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(409);

        $this->response->expects($this->once())
            ->method('getReasonPhrase')
            ->willReturn('Conflict');

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
            ->with('POST', 'instances', [], $this->isType('string'))
            ->willReturn($this->response);

        $this->expectException(TalerException::class);
        $this->expectExceptionMessage('Instance creation failed: Conflict');

        CreateInstance::run($this->instanceClient, $this->config);
    }

    /**
     * Test unexpected status code.
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
            ->with('POST', 'instances', [], $this->isType('string'))
            ->willReturn($this->response);

        $this->expectException(TalerException::class);
        $this->expectExceptionMessage('Server error');

        CreateInstance::run($this->instanceClient, $this->config);
    }

    /**
     * Test that JSON encoding works correctly.
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
                'instances',
                [],
                $this->callback(function($jsonString) {
                    $data = json_decode($jsonString, true);
                    return isset($data['id']) && $data['id'] === 'test-instance';
                })
            )
            ->willReturn($this->response);

        CreateInstance::run($this->instanceClient, $this->config);
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
            ->with('POST', 'instances', [], $this->isType('string'))
            ->willReturn($promise);

        $result = CreateInstance::runAsync($this->instanceClient, $this->config);
        $this->assertInstanceOf(\GuzzleHttp\Promise\PromiseInterface::class, $result);
    }
}

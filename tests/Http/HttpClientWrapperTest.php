<?php
namespace Taler\Tests\Http;

use Http\Client\Exception\HttpException;
use League\Uri\Uri;
use Psr\Http\Client\ClientInterface;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;
use Taler\Http\HttpClientWrapper;
use Nyholm\Psr7\Factory\Psr17Factory;
use Http\Mock\Client as MockClient;
use Psr\Http\Message\ResponseInterface;
use League\Uri\Http;
use PHPUnit\Framework\TestCase;

class HttpClientWrapperTest extends TestCase
{
    private \Http\Mock\Client $mockClient;
    private \Nyholm\Psr7\Factory\Psr17Factory $factory;
    private TalerConfig $config;

    private const BASE_URL = 'https://backend.demo.taler.net/instances/sandbox/';
    private const AUTH_TOKEN = 'Bearer secret-token:sandbox';

    protected function setUp(): void
    {
        $this->mockClient = new MockClient();
        $this->factory = new Psr17Factory();

        //--- Note: baseUrl and authToken are not actually used
        $this->config = new TalerConfig(
            self::BASE_URL,
            self::AUTH_TOKEN
        );
    }


    protected function getWrapper(bool $wrapResponse = true): HttpClientWrapper
    {
        return new HttpClientWrapper(
            $this->config,
            $this->mockClient,
            $this->factory,
            $this->factory,
            $wrapResponse
        );
    }

    /** @test */
    public function it_sends_sync_get_request(): void
    {
        $wrapper = $this->getWrapper();
        $response = $this->factory->createResponse(200);
        $this->mockClient->addResponse($response);

        $result = $wrapper->request('GET', 'users/1');
        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /** @test */
    public function it_sends_sync_post_request_with_body(): void
    {
        $wrapper = $this->getWrapper();
        $response = $this->factory->createResponse(201);
        $this->mockClient->addResponse($response);

        $result = $wrapper->request('POST', 'users', [], '{"name":"test"}');
        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertEquals(201, $response->getStatusCode());
    }

    /** @test */
    public function it_sends_sync_patch_request_with_body(): void
    {
        $wrapper = $this->getWrapper();
        $response = $this->factory->createResponse(200);
        $this->mockClient->addResponse($response);

        $result = $wrapper->request('PATCH', 'users/1', [], '{"name":"updated"}');

        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /** @test */
    public function it_sends_sync_delete_request(): void
    {
        $wrapper = $this->getWrapper();
        $response = $this->factory->createResponse(204);
        $this->mockClient->addResponse($response);

        $result = $wrapper->request('DELETE', 'users/1');
        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertEquals(204, $response->getStatusCode());

        $lastRequest = $this->mockClient->getLastRequest();
        $this->assertEquals('DELETE', $lastRequest->getMethod());
        $this->assertEquals(self::BASE_URL . 'users/1', (string)$lastRequest->getUri());
    }

    /** @test */
    public function it_sends_async_get_request(): void
    {
        $wrapper = $this->getWrapper();
        $this->mockClient->addResponse($this->factory->createResponse(200));

        $promise = $wrapper->requestAsync('GET', 'users/1');
        $response = $promise->wait();

        $result = $this->mockClient->getLastRequest();

        $this->assertNotNull($result);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /** @test */
    public function it_sends_async_post_request_with_body(): void
    {
        $wrapper = $this->getWrapper();
        $this->mockClient->addResponse($this->factory->createResponse(201));

        $promise = $wrapper->requestAsync('POST', 'users', [], '{"name":"test"}');
        $response = $promise->wait();
        $result = $this->mockClient->getLastRequest();

        $this->assertNotNull($result);
        $this->assertEquals(201, $response->getStatusCode());

        // Verify request details
        $this->assertEquals('POST', $result->getMethod());
        $this->assertEquals(self::BASE_URL . 'users', (string)$result->getUri());
        $this->assertJsonStringEqualsJsonString(
            '{"name":"test"}',
            (string)$result->getBody()
        );

    }


    /** @test */
    public function it_sends_async_patch_request_with_body(): void
    {
        $wrapper = $this->getWrapper();
        $this->mockClient->addResponse($this->factory->createResponse(200));

        $promise = $wrapper->requestAsync('PATCH', 'users/1', [], '{"name":"updated"}');
        $response = $promise->wait();
        $result = $this->mockClient->getLastRequest();

        $this->assertNotNull($result);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['name' => 'updated'], json_decode((string)$result->getBody(), true));

        // Verify request details
        $result = $this->mockClient->getLastRequest();
        $this->assertEquals('PATCH', $result->getMethod());
        $this->assertEquals(self::BASE_URL . 'users/1', (string)$result->getUri());
        $this->assertJsonStringEqualsJsonString(
            '{"name":"updated"}',
            (string)$result->getBody()
        );

    }

    /** @test */
    public function it_sends_async_delete_request(): void
    {
        $wrapper = $this->getWrapper();
        $this->mockClient->addResponse($this->factory->createResponse(204));

        $promise = $wrapper->requestAsync('DELETE', 'users/1');
        $response = $promise->wait();
        $result = $this->mockClient->getLastRequest();

        $this->assertNotNull($result);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $response);
        $this->assertEquals(204, $response->getStatusCode());

        $this->assertEquals('DELETE', $result->getMethod());
        $this->assertEquals(self::BASE_URL . 'users/1', (string)$result->getUri());
    }

    /** @test */
    public function it_wraps_responses_when_configured(): void
    {
        $wrapper = $this->getWrapper();
        $this->mockClient->addResponse($this->factory->createResponse(200));

        $response = $wrapper->request('GET', 'users');
        $this->assertInstanceOf(\Taler\Http\Response::class, $response);
    }

    /** @test */
    public function it_builds_correct_url(): void
    {
        $wrapper = $this->getWrapper();
        $method = new \ReflectionMethod($wrapper, 'buildUrl');

        $result = $method->invoke($wrapper, 'users');
        $this->assertEquals(self::BASE_URL . 'users', $result);
    }

    /** @test */
    public function it_creates_request(): void
    {
        $wrapper = $this->getWrapper();
        $method = new \ReflectionMethod($wrapper, 'createRequest');

        $request = $method->invoke($wrapper, 'GET', 'users', ['Accept' => 'application/json']);
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals(self::BASE_URL . 'users', (string) $request->getUri());
        $this->assertEquals('application/json', $request->getHeaderLine('Accept'));
    }

    /** @test */
    public function it_validates_final_url(): void
    {
        $wrapper = $this->getWrapper();
        $method = new \ReflectionMethod($wrapper, 'validateFinalUrl');

        // Valid case
        $uri = Uri::fromBaseUri(self::BASE_URL . 'users');
        $method->invoke($wrapper, 'users', $uri);

        // Test for exception on bad endpoint
        $this->expectException(\InvalidArgumentException::class);
        $uri = Uri::fromBaseUri('https://evil.com/users');
        $method->invoke($wrapper, 'users', $uri);
    }

    /** @test */
    public function it_throws_taler_exception_on_error_when_wrapped(): void
    {
        $wrapper = $this->getWrapper();

        $this->mockClient->addException(new HttpException(
            'Error',
            $this->createMock(\Psr\Http\Message\RequestInterface::class),
            $this->factory->createResponse(400)
        ));

        $this->expectException(TalerException::class);

        $wrapper->request('GET', 'invalid-endpoint');
    }

    /** @test */
    public function it_passes_through_exceptions_when_not_wrapped(): void
    {
        $wrapper = $this->getWrapper(false);

        $this->mockClient->addException(new HttpException(
            'Error',
            $this->createMock(\Psr\Http\Message\RequestInterface::class),
            $this->factory->createResponse(400)
        ));

        $this->expectException(HttpException::class);

        $wrapper->request('GET', 'invalid-endpoint');
    }


    /** @test */
    public function async_request_fails_without_async_support(): void
    {
        $nonAsyncClient = $this->createMock(ClientInterface::class);
        $wrapper = new HttpClientWrapper($this->config, $nonAsyncClient);

        $this->expectException(\RuntimeException::class);
        $wrapper->requestAsync('GET', 'users');
    }

    /** @test */
    public function it_includes_auth_header(): void
    {
        $wrapper = $this->getWrapper();
        $this->mockClient->addResponse($this->factory->createResponse(200));

        $wrapper->request('GET', 'users');

        $lastRequest = $this->mockClient->getLastRequest();
        $this->assertEquals('Bearer secret-token:sandbox', $lastRequest->getHeader('Authorization')[0]);
    }

    /** @test */
    public function it_uses_correct_user_agent(): void
    {
        $wrapper = $this->getWrapper();
        $this->mockClient->addResponse($this->factory->createResponse(200));

        $wrapper->request('GET', 'users');

        $lastRequest = $this->mockClient->getLastRequest();
        $this->assertStringStartsWith('Mirrorps_Taler_PHP', $lastRequest->getHeader('User-Agent')[0]);
        $this->assertStringContainsString('https://github.com/mirrorps/taler-php', $lastRequest->getHeader('User-Agent')[0]);
    }

    /**
     * @test
     * @dataProvider maliciousUrlProvider
     */
    public function it_throws_exception_for_malicious_url(string $maliciousEndpoint): void
    {
        $wrapper = $this->getWrapper();

        $this->expectException(TalerException::class);

        $errorMessage = $maliciousEndpoint === '..%2Fmalicious'
            ? 'Encoded slashes are not allowed in endpoints.'
            : 'Endpoint results in a URL outside the configured base path';

        $this->expectExceptionMessage($errorMessage);

        $wrapper->request('GET', $maliciousEndpoint);

    }

    /**
     * @return array<string, array<string>>
     */
    public function maliciousUrlProvider(): array
    {
        return [
            'Path traversal' => ['../malicious'],
            'Localhost' => ['http://localhost'],
            'Internal service' => ['http://internal-service'],
            'Absolute path' => ['/etc/passwd'],
            'Encoded slashes' => ['..%2Fmalicious'], // %2F = /
        ];
    }
}
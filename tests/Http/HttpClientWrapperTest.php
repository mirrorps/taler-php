<?php
namespace Taler\Tests\Http;

use Http\Client\Exception\HttpException;
use League\Uri\Uri;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Taler\Config\TalerConfig;
use Taler\Exception\TalerException;
use Taler\Http\HttpClientWrapper;
use Nyholm\Psr7\Factory\Psr17Factory;
use Http\Mock\Client as MockClient;
use Psr\Http\Message\ResponseInterface;
use League\Uri\Http;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Monolog\Logger as MonoLogger;
use Monolog\Handler\TestHandler;

class HttpClientWrapperTest extends TestCase
{
    private \Http\Mock\Client $mockClient;
    private \Nyholm\Psr7\Factory\Psr17Factory $factory;
    private TalerConfig $config;
    private LoggerInterface $logger;

    private const BASE_URL = 'https://backend.demo.taler.net/instances/sandbox/';
    private const AUTH_TOKEN = 'Bearer secret-token:sandbox';

    protected function setUp(): void
    {
        $this->mockClient = new MockClient();
        $this->factory = new Psr17Factory();
        $this->logger = new NullLogger();

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
            $this->logger,
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
            $this->factory->createRequest('GET', 'invalid-endpoint'),
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
            $this->factory->createRequest('GET', 'invalid-endpoint'),
            $this->factory->createResponse(400)
        ));

        $this->expectException(HttpException::class);

        $wrapper->request('GET', 'invalid-endpoint');
    }

    /** @test */
    public function async_request_fails_without_async_support(): void
    {
        /** @var ClientInterface&MockObject */
        $client = $this->createMock(ClientInterface::class);
        $wrapper = new HttpClientWrapper(
            $this->config,
            $client,
            $this->logger,
            $this->factory,
            $this->factory,
            true
        );

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

        $this->assertEquals(self::AUTH_TOKEN, $lastRequest->getHeaderLine('Authorization'));
    }

    /** @test */
    public function it_logs_response_body_and_sanitizes_and_truncates(): void
    {
        // Enable SDK debug logging so logging code paths execute
        $this->config->setAttribute('debugLoggingEnabled', true);

        $testHandler = new TestHandler(MonoLogger::DEBUG);
        $monoLogger = new MonoLogger('test');
        $monoLogger->pushHandler($testHandler);

        $wrapper = new HttpClientWrapper(
            $this->config,
            $this->mockClient,
            $monoLogger,
            $this->factory,
            $this->factory,
            true
        );

        $payload = json_encode([
            'access_token' => 'abc123',
            'details' => ['password' => 'top-secret', 'note' => 'ok']
        ], JSON_THROW_ON_ERROR);
        $response = $this->factory->createResponse(200)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Set-Cookie', 'sid=xyz; HttpOnly')
            ->withHeader('Location', 'https://example.com/callback?access_token=abc123&merchant_sig=deadbeef');
        $response = $response->withBody($this->factory->createStream($payload));
        $this->mockClient->addResponse($response);

        $wrapper->request('GET', 'config');

        $records = $testHandler->getRecords();

        $responseHeadersRecord = null;
        $responseBodyRecord = null;
        foreach ($records as $rec) {
            if (isset($rec['message']) && $rec['message'] === 'Taler response headers: ') {
                $responseHeadersRecord = $rec;
            }
            if (isset($rec['message']) && str_starts_with($rec['message'], 'Taler response body: ')) {
                $responseBodyRecord = $rec;
            }
        }

        $this->assertNotNull($responseHeadersRecord, 'Response headers were not logged');
        $this->assertArrayHasKey('Set-Cookie', $responseHeadersRecord['context']);
        $this->assertSame(['***'], $responseHeadersRecord['context']['Set-Cookie']);
        // Location header should be present and have redacted query parameters
        $this->assertArrayHasKey('Location', $responseHeadersRecord['context']);
        $locationJoined = implode(',', $responseHeadersRecord['context']['Location']);
        $decoded = urldecode($locationJoined);
        $this->assertStringNotContainsString('abc123', $decoded);
        $this->assertStringNotContainsString('deadbeef', $decoded);
        $this->assertStringContainsString('access_token=***', $decoded);
        $this->assertStringContainsString('merchant_sig=***', $decoded);

        $this->assertNotNull($responseBodyRecord, 'Response body was not logged');
        $this->assertStringNotContainsString('abc123', $responseBodyRecord['message']);
        $this->assertStringNotContainsString('top-secret', $responseBodyRecord['message']);
        $this->assertStringContainsString('"access_token":"***"', $responseBodyRecord['message']);
        $this->assertStringContainsString('"password":"***"', $responseBodyRecord['message']);
    }

    /** @test */
    public function it_uses_correct_user_agent(): void
    {
        $wrapper = $this->getWrapper();
        $this->mockClient->addResponse($this->factory->createResponse(200));

        $wrapper->request('GET', 'users');
        $lastRequest = $this->mockClient->getLastRequest();

        $this->assertStringContainsString('Mirrorps_Taler_PHP', $lastRequest->getHeaderLine('User-Agent'));
    }

    /**
     * @test
     * @dataProvider maliciousUrlProvider
     */
    public function it_throws_exception_for_malicious_url(string $maliciousEndpoint): void
    {
        $wrapper = $this->getWrapper();
        $this->mockClient->addResponse($this->factory->createResponse(200));

        $this->expectException(TalerException::class);
        $wrapper->request('GET', $maliciousEndpoint);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function maliciousUrlProvider(): array
    {
        return [
            'Path traversal' => ['../etc/passwd'],
            'Localhost' => ['http://localhost/test'],
            'Encoded slashes' => ['test%2Fpath'],
            'Internal service' => ['http://internal-service/api'],
            'Absolute path' => ['/etc/passwd'],
        ];
    }
}
<?php
namespace Taler\Tests\Http;

use GuzzleHttp\Middleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Taler\Http\HttpClientWrapper;
use Taler\Config\TalerConfig;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Promise\Promise;
use Taler\Exception\TalerException;
use Psr\Http\Message\ResponseInterface;

class HttpClientWrapperTest extends TestCase
{
    private TalerConfig $config;
    private MockHandler $mockHandler;
    private Client $client;

    private const BASE_URL = 'https://backend.demo.taler.net/instances/sandbox';
    private const AUTH_TOKEN = 'Bearer secret-token:sandbox';

    protected function setUp(): void
    {
        /**
         * Note: baseUrl and authToken not actually used
         */
        $this->config = new TalerConfig(
            self::BASE_URL,
            self::AUTH_TOKEN
        );

        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        $this->client = new Client(['handler' => $handlerStack]);
    }

    private function createWrapper(bool $wrapResponse = true): HttpClientWrapper
    {
        return new HttpClientWrapper(
            $this->config,
            $this->client,
            [],
            $wrapResponse
        );
    }

    /** @test */
    public function it_sends_sync_get_request(): void
    {
        $wrapper = $this->createWrapper();
        $this->mockHandler->append(new Response(200, [], 'OK'));

        $response = $wrapper->request('GET', 'users');

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', (string)$response->getBody());
    }

    /** @test */
    public function it_sends_sync_post_request_with_body(): void
    {
        $wrapper = $this->createWrapper();
        $this->mockHandler->append(new Response(201));

        $response = $wrapper->request('POST', 'users', [
            'json' => ['name' => 'John']
        ]);

        $this->assertEquals(201, $response->getStatusCode());
    }

    /** @test */
    public function it_sends_sync_patch_request_with_body(): void
    {
        $wrapper = $this->createWrapper();
        $this->mockHandler->append(new Response(201));

        $response = $wrapper->request('PATCH', 'users', [
            'json' => ['name' => 'John']
        ]);

        $this->assertEquals(201, $response->getStatusCode());
    }

    /** @test */
    public function it_sends_sync_delete_request(): void
    {
        $wrapper = $this->createWrapper();
        $this->mockHandler->append(new Response(204));

        $response = $wrapper->request('DELETE', 'resource/123');

        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $response);
        $this->assertEquals(204, $response->getStatusCode());

        $lastRequest = $this->mockHandler->getLastRequest();
        $this->assertEquals('DELETE', $lastRequest->getMethod());
        $this->assertEquals(self::BASE_URL . '/resource/123', (string)$lastRequest->getUri());
    }

    /** @test */
    public function it_sends_async_get_request(): void
    {
        $wrapper = $this->createWrapper();
        $this->mockHandler->append(new Response(200));

        $promise = $wrapper->requestAsync('GET', 'users');
        $response = $promise->wait();

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /** @test */
    public function it_sends_async_post_request_with_body(): void
    {
        $wrapper = $this->createWrapper();
        $jsonData = json_encode(['id' => 123]);
        if ($jsonData === false) {
            $this->fail('Failed to encode JSON data');
        }
        $this->mockHandler->append(new Response(201, [], $jsonData));

        $promise = $wrapper->requestAsync('POST', 'users', [
            'json' => ['name' => 'John']
        ]);

        $response = $promise->wait();

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals(['id' => 123], json_decode((string)$response->getBody(), true));

        // Verify request details
        $lastRequest = $this->mockHandler->getLastRequest();
        $this->assertEquals('POST', $lastRequest->getMethod());
        $this->assertEquals(self::BASE_URL . '/users', (string)$lastRequest->getUri());
        $this->assertJsonStringEqualsJsonString(
            '{"name":"John"}',
            (string)$lastRequest->getBody()
        );
    }

    /** @test */
    public function it_sends_async_patch_request_with_body(): void
    {
        $wrapper = $this->createWrapper();
        $jsonData = json_encode(['status' => 'updated']);
        if ($jsonData === false) {
            $this->fail('Failed to encode JSON data');
        }
        $this->mockHandler->append(new Response(200, [], $jsonData));

        $promise = $wrapper->requestAsync('PATCH', 'users/123', [
            'json' => ['name' => 'Updated Name']
        ]);

        $response = $promise->wait();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['status' => 'updated'], json_decode((string)$response->getBody(), true));

        // Verify request details
        $lastRequest = $this->mockHandler->getLastRequest();
        $this->assertEquals('PATCH', $lastRequest->getMethod());
        $this->assertEquals(self::BASE_URL . '/users/123', (string)$lastRequest->getUri());
        $this->assertJsonStringEqualsJsonString(
            '{"name":"Updated Name"}',
            (string)$lastRequest->getBody()
        );
    }

    /** @test */
    public function it_sends_async_delete_request(): void
    {
        $wrapper = $this->createWrapper();
        $this->mockHandler->append(new Response(204));

        $promise = $wrapper->requestAsync('DELETE', 'resource/123');
        $response = $promise->wait();

        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $response);
        $this->assertEquals(204, $response->getStatusCode());

        $lastRequest = $this->mockHandler->getLastRequest();
        $this->assertEquals('DELETE', $lastRequest->getMethod());
        $this->assertEquals(self::BASE_URL . '/resource/123', (string)$lastRequest->getUri());
    }

    /** @test */
    public function it_wraps_responses_when_configured(): void
    {
        $wrapper = $this->createWrapper();
        $this->mockHandler->append(new Response(200));

        $response = $wrapper->request('GET', 'users');
        $this->assertInstanceOf(\Taler\Http\Response::class, $response);
    }

    /** @test */
    public function it_throws_taler_exception_on_error_when_wrapped(): void
    {
        $wrapper = $this->createWrapper();
        $this->mockHandler->append(new \GuzzleHttp\Exception\ClientException(
            'Error',
            $this->createMock(\Psr\Http\Message\RequestInterface::class),
            new Response(400)
        ));

        $this->expectException(TalerException::class);
        $wrapper->request('GET', 'invalid-endpoint');
    }

    /** @test */
    public function it_passes_through_exceptions_when_not_wrapped(): void
    {
        $wrapper = $this->createWrapper(false);
        $this->mockHandler->append(new \GuzzleHttp\Exception\ClientException(
            'Error',
            $this->createMock(\Psr\Http\Message\RequestInterface::class),
            new Response(400)
        ));

        $this->expectException(\GuzzleHttp\Exception\ClientException::class);
        $wrapper->request('GET', 'invalid-endpoint');
    }

    /** @test */
    public function it_merges_client_options(): void
    {
        $container = [];
        $history = Middleware::history($container);

        $mock = new MockHandler([new Response(200)]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $client = new \GuzzleHttp\Client(['handler' => $handlerStack]);

        $config = new \Taler\Config\TalerConfig(self::BASE_URL);

        $wrapper = new \Taler\Http\HttpClientWrapper(
            $config,
            $client,
            ['body' => 'test'],
            true
        );

        $wrapper->request('GET', 'users', ['connect_timeout' => 5]);

        // Now inspect the options used in the request
        $this->assertNotEmpty($container);
        $transaction = $container[0];
        $this->assertEquals('test', (string)$transaction['request']->getBody());
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
        $wrapper = $this->createWrapper();
        $this->mockHandler->append(new Response(200));

        $wrapper->request('GET', 'users');

        $lastRequest = $this->mockHandler->getLastRequest();
        $this->assertEquals('Bearer secret-token:sandbox', $lastRequest->getHeader('Authorization')[0]);
    }

    /** @test */
    public function it_uses_correct_user_agent(): void
    {
        $wrapper = $this->createWrapper();
        $this->mockHandler->append(new Response(200));

        $wrapper->request('GET', 'users');

        $lastRequest = $this->mockHandler->getLastRequest();
        $this->assertStringStartsWith('Mirrorps_Taler_PHP', $lastRequest->getHeader('User-Agent')[0]);
        $this->assertStringContainsString('https://github.com/mirrorps/taler-php', $lastRequest->getHeader('User-Agent')[0]);
    }

    /** @test */
    public function it_builds_correct_url(): void
    {
        $wrapper = $this->createWrapper();
        $this->mockHandler->append(new Response(200));

        $wrapper->request('GET', 'users');

        $lastRequest = $this->mockHandler->getLastRequest();
        $this->assertEquals(self::BASE_URL . '/users', (string)$lastRequest->getUri());
    }
}
<?php
namespace Taler\Tests\Http;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Taler\Http\Response;

class ResponseTest extends TestCase
{
    /**
     * @param array<string, array<int, string>> $headers
     * @return ResponseInterface
     */
    private function createMockResponse(string $body = '{"foo":"bar"}', int $status = 200, array $headers = ['Content-Type' => ['application/json']]): ResponseInterface
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getContents')->willReturn($body);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);
        $response->method('getStatusCode')->willReturn($status);
        $response->method('getHeaders')->willReturn($headers);
        $response->method('hasHeader')->willReturnCallback(fn($name) => isset($headers[$name]));
        $response->method('getHeader')->willReturnCallback(fn($name) => $headers[$name] ?? []);
        $response->method('getHeaderLine')->willReturnCallback(fn($name) => isset($headers[$name]) ? implode(', ', $headers[$name]) : '');
        $response->method('getProtocolVersion')->willReturn('1.1');
        $response->method('getReasonPhrase')->willReturn('OK');
        $response->method('withProtocolVersion')->willReturn($response);
        $response->method('withHeader')->willReturn($response);
        $response->method('withAddedHeader')->willReturn($response);
        $response->method('withoutHeader')->willReturn($response);
        $response->method('withBody')->willReturn($response);
        $response->method('withStatus')->willReturn($response);

        return $response;
    }

    /**
     * Test that JSON response body is decoded to data property
     */
    public function test_it_decodes_json_body_to_data(): void
    {
        $mockResponse = $this->createMockResponse('{"foo":"bar"}');
        $response = new Response($mockResponse);

        $this->assertEquals((object)['foo' => 'bar'], $response->getData());
        $this->assertEquals((object)['foo' => 'bar'], $response->data);
    }

    public function test_it_returns_null_for_empty_body():void
    {
        $mockResponse = $this->createMockResponse('');
        $response = new Response($mockResponse);

        $this->assertNull($response->getData());
        $this->assertNull($response->data);
    }

    public function test_it_delegates_getters_to_wrapped_response():void
    {
        $mockResponse = $this->createMockResponse('{"foo":"bar"}', 201, [
            'Content-Type' => ['application/json'],
            'X-Test' => ['abc']
        ]);
        $response = new Response($mockResponse);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
        $this->assertEquals('1.1', $response->getProtocolVersion());
        $this->assertEquals(['Content-Type' => ['application/json'], 'X-Test' => ['abc']], $response->getHeaders());
        $this->assertTrue($response->hasHeader('Content-Type'));
        $this->assertEquals(['abc'], $response->getHeader('X-Test'));
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
    }

    public function test_it_delegates_with_methods_to_wrapped_response():void
    {
        $mockResponse = $this->createMockResponse();
        $response = new Response($mockResponse);

        // Test immutability - each call should return a new instance
        $newResponse1 = $response->withProtocolVersion('2.0');
        $this->assertInstanceOf(Response::class, $newResponse1);
        $this->assertNotSame($response, $newResponse1);

        $newResponse2 = $response->withHeader('X-Test', 'val');
        $this->assertInstanceOf(Response::class, $newResponse2);
        $this->assertNotSame($response, $newResponse2);

        $newResponse3 = $response->withAddedHeader('X-Test', 'val2');
        $this->assertInstanceOf(Response::class, $newResponse3);
        $this->assertNotSame($response, $newResponse3);

        $newResponse4 = $response->withoutHeader('X-Test');
        $this->assertInstanceOf(Response::class, $newResponse4);
        $this->assertNotSame($response, $newResponse4);

        /** @var StreamInterface&MockObject $mockStream */
        $mockStream = $this->createMock(StreamInterface::class);
        $newResponse5 = $response->withBody($mockStream);
        $this->assertInstanceOf(Response::class, $newResponse5);
        $this->assertNotSame($response, $newResponse5);

        $newResponse6 = $response->withStatus(404, 'Not Found');
        $this->assertInstanceOf(Response::class, $newResponse6);
        $this->assertNotSame($response, $newResponse6);

        // Verify that original response is unchanged
        $this->assertEquals('1.1', $response->getProtocolVersion());
    }

    public function test_get_body_returns_stream():void
    {
        /** @var StreamInterface&MockObject $stream */
        $stream = $this->createMock(StreamInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getBody')->willReturn($stream);

        $response = new Response($mockResponse);
        $this->assertSame($stream, $response->getBody());
    }
}
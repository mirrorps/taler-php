<?php
namespace Taler\Tests\Http;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Taler\Http\Response;

class ResponseTest extends TestCase
{
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

    public function test_it_decodes_json_body_to_data()
    {
        $mockResponse = $this->createMockResponse('{"foo":"bar"}');
        $response = new Response($mockResponse);

        $this->assertEquals((object)['foo' => 'bar'], $response->getData());
        $this->assertEquals((object)['foo' => 'bar'], $response->data);
    }

    public function test_it_returns_null_for_empty_body()
    {
        $mockResponse = $this->createMockResponse('');
        $response = new Response($mockResponse);

        $this->assertNull($response->getData());
        $this->assertNull($response->data);
    }

    public function test_it_delegates_getters_to_wrapped_response()
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

    public function test_it_delegates_with_methods_to_wrapped_response()
    {
        $mockResponse = $this->createMockResponse();
        $response = new Response($mockResponse);

        $this->assertSame($mockResponse, $response->withProtocolVersion('2.0'));
        $this->assertSame($mockResponse, $response->withHeader('X-Test', 'val'));
        $this->assertSame($mockResponse, $response->withAddedHeader('X-Test', 'val2'));
        $this->assertSame($mockResponse, $response->withoutHeader('X-Test'));
        $this->assertSame($mockResponse, $response->withBody($this->createMock(StreamInterface::class)));
        $this->assertSame($mockResponse, $response->withStatus(404, 'Not Found'));
    }

    public function test_get_body_returns_stream()
    {
        $stream = $this->createMock(StreamInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getBody')->willReturn($stream);

        $response = new Response($mockResponse);
        $this->assertSame($stream, $response->getBody());
    }
}
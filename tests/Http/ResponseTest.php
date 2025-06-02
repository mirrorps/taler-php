<?php
namespace Taler\Tests\Http;

use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Taler\Http\Response;

class ResponseTest extends TestCase
{
    /**
     * @param array<string, string[]> $headers
     * @throws Exception
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

    public function test_it_decodes_json_body_to_data():void
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

    public function test_it_delegates_with_methods_to_wrapped_response(): void
    {
        $originalMock = $this->createMock(ResponseInterface::class);

        // Create new mocks for each "with*" method to return
        $withProtocolVersionMock = $this->createMock(ResponseInterface::class);
        $withHeaderMock = $this->createMock(ResponseInterface::class);
        $withAddedHeaderMock = $this->createMock(ResponseInterface::class);
        $withoutHeaderMock = $this->createMock(ResponseInterface::class);
        $withBodyMock = $this->createMock(ResponseInterface::class);
        $withStatusMock = $this->createMock(ResponseInterface::class);

        // Set up expectations for delegation
        $originalMock->expects($this->once())
                     ->method('withProtocolVersion')
                     ->with('2.0')
                     ->willReturn($withProtocolVersionMock);

        $originalMock->expects($this->once())
                     ->method('withHeader')
                     ->with('X-Test', 'val')
                     ->willReturn($withHeaderMock);

        $originalMock->expects($this->once())
                     ->method('withAddedHeader')
                     ->with('X-Test', 'val2')
                     ->willReturn($withAddedHeaderMock);

        $originalMock->expects($this->once())
                     ->method('withoutHeader')
                     ->with('X-Test')
                     ->willReturn($withoutHeaderMock);

        $streamMock = $this->createMock(StreamInterface::class);
        $originalMock->expects($this->once())
                     ->method('withBody')
                     ->with($streamMock)
                     ->willReturn($withBodyMock);

        $originalMock->expects($this->once())
                     ->method('withStatus')
                     ->with(404, 'Not Found')
                     ->willReturn($withStatusMock);

        $response = new Response($originalMock);

        // Test withProtocolVersion
        $newResponse = $response->withProtocolVersion('2.0');
        $this->assertInstanceOf(Response::class, $newResponse);
        $this->assertNotSame($response, $newResponse);

        // Use reflection or add a getter to check the wrapped response if needed
        $wrapped = (fn($r) => $r->response)->call($newResponse, $newResponse);
        $this->assertSame($withProtocolVersionMock, $wrapped);

        // Test withHeader
        $newResponse = $response->withHeader('X-Test', 'val');
        $this->assertInstanceOf(Response::class, $newResponse);
        $this->assertNotSame($response, $newResponse);
        $wrapped = (fn($r) => $r->response)->call($newResponse, $newResponse);
        $this->assertSame($withHeaderMock, $wrapped);

        // Test withAddedHeader
        $newResponse = $response->withAddedHeader('X-Test', 'val2');
        $this->assertInstanceOf(Response::class, $newResponse);
        $this->assertNotSame($response, $newResponse);
        $wrapped = (fn($r) => $r->response)->call($newResponse, $newResponse);
        $this->assertSame($withAddedHeaderMock, $wrapped);

        // Test withoutHeader
        $newResponse = $response->withoutHeader('X-Test');
        $this->assertInstanceOf(Response::class, $newResponse);
        $this->assertNotSame($response, $newResponse);
        $wrapped = (fn($r) => $r->response)->call($newResponse, $newResponse);
        $this->assertSame($withoutHeaderMock, $wrapped);

        // Test withBody
        $newResponse = $response->withBody($streamMock);
        $this->assertInstanceOf(Response::class, $newResponse);
        $this->assertNotSame($response, $newResponse);
        $wrapped = (fn($r) => $r->response)->call($newResponse, $newResponse);
        $this->assertSame($withBodyMock, $wrapped);

        // Test withStatus
        $newResponse = $response->withStatus(404, 'Not Found');
        $this->assertInstanceOf(Response::class, $newResponse);
        $this->assertNotSame($response, $newResponse);
        $wrapped = (fn($r) => $r->response)->call($newResponse, $newResponse);
        $this->assertSame($withStatusMock, $wrapped);
    }

    public function test_get_body_returns_stream():void
    {
        $stream = $this->createMock(StreamInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getBody')->willReturn($stream);

        $response = new Response($mockResponse);
        $this->assertEquals($stream, $response->getBody());
    }
}
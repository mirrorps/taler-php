<?php
namespace Taler\Tests\Exception;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Taler\Exception\TalerException;
use Taler\Api\Dto\ErrorDetail;

class TalerExceptionTest extends TestCase
{
    /**
     * Create a mocked PSR-7 Response with a given body string
     *
     * @return ResponseInterface&MockObject
     */
    private function createMockResponse(string $body)
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('__toString')->willReturn($body);
        $stream->method('getContents')->willReturn($body);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        return $response;
    }

    public function test_constructor_sanitizes_message_and_stores_response(): void
    {
        $rawMessage = 'Authorization: Bearer super-secret-token error happened';
        $response = $this->createMockResponse('{"code":1234,"hint":"oops"}');

        $ex = new TalerException($rawMessage, 409, null, $response);

        $this->assertNotSame($rawMessage, $ex->getMessage());
        $this->assertStringContainsString('Authorization: Bearer ***', $ex->getMessage());
        $this->assertSame($response, $ex->getResponse());
    }

    public function test_get_raw_response_body_returns_string(): void
    {
        $response = $this->createMockResponse('{"ok":true}');
        $ex = new TalerException('msg', 400, null, $response);

        $this->assertSame('{"ok":true}', $ex->getRawResponseBody());
    }

    public function test_get_response_json_decodes_to_array(): void
    {
        $payload = json_encode([
            'code' => 1000,
            'hint' => 'Bad request',
        ], JSON_THROW_ON_ERROR);

        $response = $this->createMockResponse($payload);
        $ex = new TalerException('msg', 400, null, $response);

        $json = $ex->getResponseJson();
        $this->assertIsArray($json);
        $this->assertSame(1000, $json['code']);
        $this->assertSame('Bad request', $json['hint']);
    }

    public function test_get_response_json_returns_null_when_no_response(): void
    {
        $ex = new TalerException('msg');
        $this->assertNull($ex->getResponseJson());
        $this->assertNull($ex->getRawResponseBody());
    }

    public function test_get_response_dto_parses_error_detail(): void
    {
        $payload = json_encode([
            'code' => 4090,
            'hint' => 'Conflict',
            'detail' => 'Instance exists',
            'parameter' => 'id',
            'path' => '/instances',
            'offset' => '0',
            'index' => '0',
            'object' => 'instance',
            'currency' => 'TESTKUDOS',
            'type_expected' => 'string',
            'type_actual' => 'number',
            'extra' => ['k' => 'v'],
        ], JSON_THROW_ON_ERROR);

        $response = $this->createMockResponse($payload);
        $ex = new TalerException('msg', 409, null, $response);

        $dto = $ex->getResponseDTO();
        $this->assertNotNull($dto);
        $this->assertSame(4090, $dto->code);
        $this->assertSame('Conflict', $dto->hint);
        $this->assertSame('Instance exists', $dto->detail);
        $this->assertSame('id', $dto->parameter);
        $this->assertSame('/instances', $dto->path);
        $this->assertSame('0', $dto->offset);
        $this->assertSame('0', $dto->index);
        $this->assertSame('instance', $dto->object);
        $this->assertSame('TESTKUDOS', $dto->currency);
        $this->assertSame('string', $dto->type_expected);
        $this->assertSame('number', $dto->type_actual);
        $this->assertIsArray($dto->extra);
        $this->assertSame('v', $dto->extra['k']);
    }

    public function test_get_response_dto_returns_null_for_non_json_body(): void
    {
        $response = $this->createMockResponse('not-json');
        $ex = new TalerException('msg', 500, null, $response);

        $dto = $ex->getResponseDTO();
        $this->assertNull($dto);
    }

    public function test_get_response_dto_returns_error_detail_instance(): void
    {
        $payload = json_encode([
            'code' => 1,
        ], JSON_THROW_ON_ERROR);

        $response = $this->createMockResponse($payload);
        $ex = new TalerException('msg', 400, null, $response);

        $dto = $ex->getResponseDTO();
        $this->assertInstanceOf(ErrorDetail::class, $dto);
        $this->assertSame(1, $dto->code);
    }
}



<?php

namespace Taler\Tests\Api\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\ErrorDetail;

class ErrorDetailTest extends TestCase
{
    /**
     * @var array{
     *     code: int,
     *     hint: string,
     *     detail: string,
     *     parameter: string,
     *     path: string,
     *     offset: string,
     *     index: string,
     *     object: string,
     *     currency: string,
     *     type_expected: string,
     *     type_actual: string,
     *     extra: array<string, string>
     * }
     */
    private array $fullData = [
        'code' => 1001,
        'hint' => 'Invalid input',
        'detail' => 'The provided value is not valid',
        'parameter' => 'amount',
        'path' => '/payment/amount',
        'offset' => '10',
        'index' => '2',
        'object' => 'Payment',
        'currency' => 'USD',
        'type_expected' => 'number',
        'type_actual' => 'string',
        'extra' => ['additional' => 'info']
    ];

    public function testConstructorWithRequiredOnly(): void
    {
        $error = new ErrorDetail(code: 1001);

        $this->assertSame(1001, $error->code);
        $this->assertNull($error->hint);
        $this->assertNull($error->detail);
        $this->assertNull($error->parameter);
        $this->assertNull($error->path);
        $this->assertNull($error->offset);
        $this->assertNull($error->index);
        $this->assertNull($error->object);
        $this->assertNull($error->currency);
        $this->assertNull($error->type_expected);
        $this->assertNull($error->type_actual);
        $this->assertNull($error->extra);
    }

    public function testConstructorWithAllParameters(): void
    {
        $error = new ErrorDetail(
            code: $this->fullData['code'],
            hint: $this->fullData['hint'],
            detail: $this->fullData['detail'],
            parameter: $this->fullData['parameter'],
            path: $this->fullData['path'],
            offset: $this->fullData['offset'],
            index: $this->fullData['index'],
            object: $this->fullData['object'],
            currency: $this->fullData['currency'],
            type_expected: $this->fullData['type_expected'],
            type_actual: $this->fullData['type_actual'],
            extra: $this->fullData['extra']
        );

        $this->assertSame($this->fullData['code'], $error->code);
        $this->assertSame($this->fullData['hint'], $error->hint);
        $this->assertSame($this->fullData['detail'], $error->detail);
        $this->assertSame($this->fullData['parameter'], $error->parameter);
        $this->assertSame($this->fullData['path'], $error->path);
        $this->assertSame($this->fullData['offset'], $error->offset);
        $this->assertSame($this->fullData['index'], $error->index);
        $this->assertSame($this->fullData['object'], $error->object);
        $this->assertSame($this->fullData['currency'], $error->currency);
        $this->assertSame($this->fullData['type_expected'], $error->type_expected);
        $this->assertSame($this->fullData['type_actual'], $error->type_actual);
        $this->assertSame($this->fullData['extra'], $error->extra);
    }

    public function testFromArrayWithRequiredOnly(): void
    {
        /** @var array{code: int} $data */
        $data = ['code' => 1001];
        $error = ErrorDetail::fromArray($data);

        $this->assertSame(1001, $error->code);
        $this->assertNull($error->hint);
        $this->assertNull($error->detail);
        $this->assertNull($error->parameter);
        $this->assertNull($error->path);
        $this->assertNull($error->offset);
        $this->assertNull($error->index);
        $this->assertNull($error->object);
        $this->assertNull($error->currency);
        $this->assertNull($error->type_expected);
        $this->assertNull($error->type_actual);
        $this->assertNull($error->extra);
    }

    public function testFromArrayWithAllParameters(): void
    {
        $error = ErrorDetail::fromArray($this->fullData);

        $this->assertSame($this->fullData['code'], $error->code);
        $this->assertSame($this->fullData['hint'], $error->hint);
        $this->assertSame($this->fullData['detail'], $error->detail);
        $this->assertSame($this->fullData['parameter'], $error->parameter);
        $this->assertSame($this->fullData['path'], $error->path);
        $this->assertSame($this->fullData['offset'], $error->offset);
        $this->assertSame($this->fullData['index'], $error->index);
        $this->assertSame($this->fullData['object'], $error->object);
        $this->assertSame($this->fullData['currency'], $error->currency);
        $this->assertSame($this->fullData['type_expected'], $error->type_expected);
        $this->assertSame($this->fullData['type_actual'], $error->type_actual);
        $this->assertSame($this->fullData['extra'], $error->extra);
    }

    public function testFromArrayWithPartialParameters(): void
    {
        /** @var array{code: int, hint: string, detail: string} $data */
        $data = [
            'code' => 1001,
            'hint' => 'Invalid input',
            'detail' => 'The provided value is not valid',
            // Omitting other optional parameters
        ];

        $error = ErrorDetail::fromArray($data);

        $this->assertSame($data['code'], $error->code);
        $this->assertSame($data['hint'], $error->hint);
        $this->assertSame($data['detail'], $error->detail);
        $this->assertNull($error->parameter);
        $this->assertNull($error->path);
        $this->assertNull($error->offset);
        $this->assertNull($error->index);
        $this->assertNull($error->object);
        $this->assertNull($error->currency);
        $this->assertNull($error->type_expected);
        $this->assertNull($error->type_actual);
        $this->assertNull($error->extra);
    }
} 
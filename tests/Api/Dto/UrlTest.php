<?php

namespace Taler\Tests\Api\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\Url;

class UrlTest extends TestCase
{
    public function testFromStringCreatesUrl(): void
    {
        $url = Url::fromString('https://example.com/webhook');

        $this->assertInstanceOf(Url::class, $url);
        $this->assertSame('https://example.com/webhook', (string) $url);
    }

    public function testJsonSerializeReturnsStringUrl(): void
    {
        $url = Url::fromString('https://example.com/webhook');

        $this->assertSame('"https:\/\/example.com\/webhook"', json_encode($url, JSON_THROW_ON_ERROR));
    }

    public function testFromStringRejectsEmptyUrl(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('url must not be empty');

        Url::fromString('');
    }

    public function testFromStringRejectsInvalidUrl(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('url must be a valid URL');

        Url::fromString('not-a-url');
    }

    public function testFromStringRejectsNonHttpScheme(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('url scheme must be http or https');

        Url::fromString('ftp://example.com/webhook');
    }
}

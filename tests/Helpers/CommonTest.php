<?php

namespace Taler\Tests\Helpers;

use PHPUnit\Framework\TestCase;
use function Taler\Helpers\isValidBaseUrl;

class CommonTest extends TestCase
{
    public function test_is_valid_base_url_accepts_valid_urls(): void
    {
        $this->assertTrue(isValidBaseUrl('https://example.com'));
    }

    public function test_is_valid_base_url_rejects_invalid_urls(): void
    {
        $this->assertFalse(isValidBaseUrl('http://example.com')); // non-https
        $this->assertFalse(isValidBaseUrl('not-a-url'));         // invalid format
        $this->assertFalse(isValidBaseUrl('ftp://example.com')); // wrong protocol
        $this->assertFalse(isValidBaseUrl(''));                  // empty string
    }
}
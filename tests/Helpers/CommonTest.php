<?php

namespace Taler\Tests\Helpers;

use PHPUnit\Framework\TestCase;
use function Taler\Helpers\isValidUrl;

class CommonTest extends TestCase
{
    public function test_is_valid_base_url_accepts_valid_urls(): void
    {
        $this->assertTrue(isValidUrl('https://example.com'));
        $this->assertTrue(isValidUrl('https://api.taler.net'));
        $this->assertTrue(isValidUrl('https://demo.taler.net/instances/sandbox'));
    }

    public function test_is_valid_base_url_rejects_invalid_urls(): void
    {
        $this->assertFalse(isValidUrl('http://example.com')); // non-https
        $this->assertFalse(isValidUrl('not-a-url'));         // invalid format
        $this->assertFalse(isValidUrl('ftp://example.com')); // wrong protocol
        $this->assertFalse(isValidUrl(''));                  // empty string
    }
}
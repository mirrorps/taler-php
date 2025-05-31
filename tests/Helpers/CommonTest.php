<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class CommonTest extends TestCase
{
    public function test_is_valid_base_url_accepts_valid_urls()
    {
        $this->assertTrue(isValidBaseUrl('https://example.com'));
    }

    public function test_is_valid_base_url_rejects_invalid_urls()
    {
        $this->assertFalse(isValidBaseUrl('ftp://example.com'));
        $this->assertFalse(isValidBaseUrl('not-a-url'));
        $this->assertFalse(isValidBaseUrl('http:/invalid.com'));
        $this->assertFalse(isValidBaseUrl(''));
    }
}
<?php

namespace Taler\Tests\Api\ContractTerms\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\ContractTerms\Dto\ContractInputToken;

class ContractInputTokenTest extends TestCase
{
    private const SAMPLE_TOKEN_FAMILY_SLUG = 'test-token-family';
    private const SAMPLE_COUNT = 5;

    public function testConstruct(): void
    {
        $contractInput = new ContractInputToken(
            token_family_slug: self::SAMPLE_TOKEN_FAMILY_SLUG
        );

        $this->assertSame(self::SAMPLE_TOKEN_FAMILY_SLUG, $contractInput->token_family_slug);
        $this->assertSame(1, $contractInput->count);
        $this->assertSame('token', $contractInput->getType());
    }

    public function testConstructWithCustomCount(): void
    {
        $contractInput = new ContractInputToken(
            token_family_slug: self::SAMPLE_TOKEN_FAMILY_SLUG,
            count: self::SAMPLE_COUNT
        );

        $this->assertSame(self::SAMPLE_TOKEN_FAMILY_SLUG, $contractInput->token_family_slug);
        $this->assertSame(self::SAMPLE_COUNT, $contractInput->count);
        $this->assertSame('token', $contractInput->getType());
    }

    public function testFromArrayWithRequiredParameters(): void
    {
        $data = [
            'token_family_slug' => self::SAMPLE_TOKEN_FAMILY_SLUG
        ];

        $contractInput = ContractInputToken::fromArray($data);

        $this->assertSame(self::SAMPLE_TOKEN_FAMILY_SLUG, $contractInput->token_family_slug);
        $this->assertSame(1, $contractInput->count);
        $this->assertSame('token', $contractInput->getType());
    }

    public function testFromArrayWithAllParameters(): void
    {
        $data = [
            'token_family_slug' => self::SAMPLE_TOKEN_FAMILY_SLUG,
            'count' => self::SAMPLE_COUNT
        ];

        $contractInput = ContractInputToken::fromArray($data);

        $this->assertSame(self::SAMPLE_TOKEN_FAMILY_SLUG, $contractInput->token_family_slug);
        $this->assertSame(self::SAMPLE_COUNT, $contractInput->count);
        $this->assertSame('token', $contractInput->getType());
    }
} 
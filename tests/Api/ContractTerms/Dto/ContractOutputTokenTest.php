<?php

namespace Taler\Tests\Api\ContractTerms\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\ContractTerms\Dto\ContractOutputToken;

class ContractOutputTokenTest extends TestCase
{
    private const SAMPLE_TOKEN_FAMILY_SLUG = 'test-token-family';
    private const SAMPLE_KEY_INDEX = 1;
    private const SAMPLE_COUNT = 5;

    public function testConstruct(): void
    {
        $contractOutput = new ContractOutputToken(
            token_family_slug: self::SAMPLE_TOKEN_FAMILY_SLUG,
            key_index: self::SAMPLE_KEY_INDEX
        );

        $this->assertSame(self::SAMPLE_TOKEN_FAMILY_SLUG, $contractOutput->token_family_slug);
        $this->assertSame(self::SAMPLE_KEY_INDEX, $contractOutput->key_index);
        $this->assertSame(1, $contractOutput->count);
        $this->assertSame('token', $contractOutput->getType());
    }

    public function testConstructWithCustomCount(): void
    {
        $contractOutput = new ContractOutputToken(
            token_family_slug: self::SAMPLE_TOKEN_FAMILY_SLUG,
            key_index: self::SAMPLE_KEY_INDEX,
            count: self::SAMPLE_COUNT
        );

        $this->assertSame(self::SAMPLE_TOKEN_FAMILY_SLUG, $contractOutput->token_family_slug);
        $this->assertSame(self::SAMPLE_KEY_INDEX, $contractOutput->key_index);
        $this->assertSame(self::SAMPLE_COUNT, $contractOutput->count);
        $this->assertSame('token', $contractOutput->getType());
    }

    public function testCreateFromArrayWithRequiredParameters(): void
    {
        $data = [
            'token_family_slug' => self::SAMPLE_TOKEN_FAMILY_SLUG,
            'key_index' => self::SAMPLE_KEY_INDEX
        ];

        $contractOutput = ContractOutputToken::createFromArray($data);

        $this->assertSame(self::SAMPLE_TOKEN_FAMILY_SLUG, $contractOutput->token_family_slug);
        $this->assertSame(self::SAMPLE_KEY_INDEX, $contractOutput->key_index);
        $this->assertSame(1, $contractOutput->count);
        $this->assertSame('token', $contractOutput->getType());
    }

    public function testCreateFromArrayWithAllParameters(): void
    {
        $data = [
            'token_family_slug' => self::SAMPLE_TOKEN_FAMILY_SLUG,
            'key_index' => self::SAMPLE_KEY_INDEX,
            'count' => self::SAMPLE_COUNT
        ];

        $contractOutput = ContractOutputToken::createFromArray($data);

        $this->assertSame(self::SAMPLE_TOKEN_FAMILY_SLUG, $contractOutput->token_family_slug);
        $this->assertSame(self::SAMPLE_KEY_INDEX, $contractOutput->key_index);
        $this->assertSame(self::SAMPLE_COUNT, $contractOutput->count);
        $this->assertSame('token', $contractOutput->getType());
    }
} 
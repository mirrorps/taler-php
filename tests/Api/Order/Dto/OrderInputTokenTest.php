<?php

namespace Taler\Tests\Api\Order\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Order\Dto\OrderInputToken;

class OrderInputTokenTest extends TestCase
{
    private const SAMPLE_TOKEN_FAMILY_SLUG = 'test-token-family';
    private const SAMPLE_COUNT = 5;

    public function testConstruct(): void
    {
        $orderInput = new OrderInputToken(
            token_family_slug: self::SAMPLE_TOKEN_FAMILY_SLUG
        );

        $this->assertSame(self::SAMPLE_TOKEN_FAMILY_SLUG, $orderInput->token_family_slug);
        $this->assertSame(1, $orderInput->count);
        $this->assertSame('token', $orderInput->getType());
    }

    public function testConstructWithCustomCount(): void
    {
        $orderInput = new OrderInputToken(
            token_family_slug: self::SAMPLE_TOKEN_FAMILY_SLUG,
            count: self::SAMPLE_COUNT
        );

        $this->assertSame(self::SAMPLE_TOKEN_FAMILY_SLUG, $orderInput->token_family_slug);
        $this->assertSame(self::SAMPLE_COUNT, $orderInput->count);
        $this->assertSame('token', $orderInput->getType());
    }

    public function testConstructWithoutValidation(): void
    {
        $orderInput = new OrderInputToken(
            token_family_slug: self::SAMPLE_TOKEN_FAMILY_SLUG,
            validate: false
        );

        $this->assertSame(self::SAMPLE_TOKEN_FAMILY_SLUG, $orderInput->token_family_slug);
        $this->assertSame(1, $orderInput->count);
    }

    public function testCreateFromArrayWithRequiredParameters(): void
    {
        $data = [
            'token_family_slug' => self::SAMPLE_TOKEN_FAMILY_SLUG
        ];

        $orderInput = OrderInputToken::createFromArray($data);

        $this->assertSame(self::SAMPLE_TOKEN_FAMILY_SLUG, $orderInput->token_family_slug);
        $this->assertSame(1, $orderInput->count);
        $this->assertSame('token', $orderInput->getType());
    }

    public function testCreateFromArrayWithAllParameters(): void
    {
        $data = [
            'token_family_slug' => self::SAMPLE_TOKEN_FAMILY_SLUG,
            'count' => self::SAMPLE_COUNT
        ];

        $orderInput = OrderInputToken::createFromArray($data);

        $this->assertSame(self::SAMPLE_TOKEN_FAMILY_SLUG, $orderInput->token_family_slug);
        $this->assertSame(self::SAMPLE_COUNT, $orderInput->count);
        $this->assertSame('token', $orderInput->getType());
    }

    public function testValidationFailsWithEmptyTokenFamilySlug(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Token family slug cannot be empty');

        new OrderInputToken(token_family_slug: '');
    }
}
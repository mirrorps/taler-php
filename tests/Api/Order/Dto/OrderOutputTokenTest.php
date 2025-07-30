<?php

namespace Taler\Tests\Api\Order\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\Timestamp;
use Taler\Api\Order\Dto\OrderOutputToken;

class OrderOutputTokenTest extends TestCase
{
    private const SAMPLE_TOKEN_FAMILY_SLUG = 'test-token-family';
    private const SAMPLE_COUNT = 5;
    private const SAMPLE_TIMESTAMP = 1234567890;

    public function testConstruct(): void
    {
        $orderOutput = new OrderOutputToken(
            token_family_slug: self::SAMPLE_TOKEN_FAMILY_SLUG
        );

        $this->assertSame(self::SAMPLE_TOKEN_FAMILY_SLUG, $orderOutput->token_family_slug);
        $this->assertSame(1, $orderOutput->count);
        $this->assertNull($orderOutput->valid_at);
        $this->assertSame('token', $orderOutput->getType());
    }

    public function testConstructWithCustomCount(): void
    {
        $orderOutput = new OrderOutputToken(
            token_family_slug: self::SAMPLE_TOKEN_FAMILY_SLUG,
            count: self::SAMPLE_COUNT
        );

        $this->assertSame(self::SAMPLE_TOKEN_FAMILY_SLUG, $orderOutput->token_family_slug);
        $this->assertSame(self::SAMPLE_COUNT, $orderOutput->count);
        $this->assertNull($orderOutput->valid_at);
        $this->assertSame('token', $orderOutput->getType());
    }

    public function testConstructWithValidAt(): void
    {
        $timestamp = new Timestamp(self::SAMPLE_TIMESTAMP);
        $orderOutput = new OrderOutputToken(
            token_family_slug: self::SAMPLE_TOKEN_FAMILY_SLUG,
            valid_at: $timestamp
        );

        $this->assertSame(self::SAMPLE_TOKEN_FAMILY_SLUG, $orderOutput->token_family_slug);
        $this->assertSame(1, $orderOutput->count);
        $this->assertSame($timestamp, $orderOutput->valid_at);
        $this->assertSame('token', $orderOutput->getType());
    }

    public function testConstructWithoutValidation(): void
    {
        $orderOutput = new OrderOutputToken(
            token_family_slug: self::SAMPLE_TOKEN_FAMILY_SLUG,
            validate: false
        );

        $this->assertSame(self::SAMPLE_TOKEN_FAMILY_SLUG, $orderOutput->token_family_slug);
        $this->assertSame(1, $orderOutput->count);
        $this->assertNull($orderOutput->valid_at);
    }

    public function testCreateFromArrayWithRequiredParameters(): void
    {
        $data = [
            'token_family_slug' => self::SAMPLE_TOKEN_FAMILY_SLUG
        ];

        $orderOutput = OrderOutputToken::createFromArray($data);

        $this->assertSame(self::SAMPLE_TOKEN_FAMILY_SLUG, $orderOutput->token_family_slug);
        $this->assertSame(1, $orderOutput->count);
        $this->assertNull($orderOutput->valid_at);
        $this->assertSame('token', $orderOutput->getType());
    }

    public function testCreateFromArrayWithAllParameters(): void
    {
        $data = [
            'token_family_slug' => self::SAMPLE_TOKEN_FAMILY_SLUG,
            'count' => self::SAMPLE_COUNT,
            'valid_at' => ['t_s' => self::SAMPLE_TIMESTAMP]
        ];

        $orderOutput = OrderOutputToken::createFromArray($data);

        $this->assertSame(self::SAMPLE_TOKEN_FAMILY_SLUG, $orderOutput->token_family_slug);
        $this->assertSame(self::SAMPLE_COUNT, $orderOutput->count);
        $this->assertInstanceOf(Timestamp::class, $orderOutput->valid_at);
        $this->assertSame(self::SAMPLE_TIMESTAMP, $orderOutput->valid_at->t_s);
        $this->assertSame('token', $orderOutput->getType());
    }

    public function testValidationFailsWithEmptyTokenFamilySlug(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Token family slug cannot be empty');

        new OrderOutputToken(token_family_slug: '');
    }
}
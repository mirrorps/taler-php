<?php

namespace Taler\Tests\Api\DonauCharity\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\DonauCharity\Dto\PostDonauRequest;

class PostDonauRequestTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = [
            'donau_url' => 'https://donau.example',
            'charity_id' => 42,
        ];

        $dto = PostDonauRequest::createFromArray($data);

        $this->assertInstanceOf(PostDonauRequest::class, $dto);
        $this->assertSame('https://donau.example', $dto->donau_url);
        $this->assertSame(42, $dto->charity_id);
        $this->assertSame($data, $dto->jsonSerialize());
    }

    public function testValidationInvalidUrl(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('donau_url must be a valid https URL');

        new PostDonauRequest('http://not-https.example', 1);
    }

    public function testValidationEmptyUrl(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('donau_url must not be empty');

        new PostDonauRequest('', 1);
    }

    public function testValidationNegativeCharityId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('charity_id must be a non-negative integer');

        new PostDonauRequest('https://donau.example', -5);
    }
}




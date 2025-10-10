<?php

namespace Taler\Tests\Api\Order\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Order\Dto\PaymentDeniedLegallyResponse;

class PaymentDeniedLegallyResponseTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = [
            'exchange_base_urls' => [
                'https://ex1.example.com',
                'https://ex2.example.com',
            ],
        ];

        $dto = PaymentDeniedLegallyResponse::createFromArray($data);

        $this->assertCount(2, $dto->exchange_base_urls);
        $this->assertSame('https://ex1.example.com', $dto->exchange_base_urls[0]);
        $this->assertSame('https://ex2.example.com', $dto->exchange_base_urls[1]);
    }
}



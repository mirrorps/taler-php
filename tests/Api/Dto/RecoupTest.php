<?php

declare(strict_types=1);

namespace Taler\Tests\Api\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\Recoup;

class RecoupTest extends TestCase
{
    private const SAMPLE_H_DENOM_PUB = 'ED25519-PUB-123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    public function testConstructor(): void
    {
        $recoup = new Recoup(
            h_denom_pub: self::SAMPLE_H_DENOM_PUB
        );

        $this->assertSame(self::SAMPLE_H_DENOM_PUB, $recoup->h_denom_pub);
    }

    public function testFromArray(): void
    {
        $data = [
            'h_denom_pub' => self::SAMPLE_H_DENOM_PUB
        ];

        $recoup = Recoup::fromArray($data);

        $this->assertSame(self::SAMPLE_H_DENOM_PUB, $recoup->h_denom_pub);
    }
} 
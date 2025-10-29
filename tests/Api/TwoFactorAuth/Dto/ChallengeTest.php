<?php

namespace Taler\Tests\Api\TwoFactorAuth\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\TwoFactorAuth\Dto\Challenge;
use Taler\Api\TwoFactorAuth\Dto\TanChannel;

final class ChallengeTest extends TestCase
{
    public function testConstructor(): void
    {
        $challenge = new Challenge(
            challenge_id: 'ch-123',
            tan_channel: TanChannel::SMS,
            tan_info: '***1234'
        );

        $this->assertSame('ch-123', $challenge->challenge_id);
        $this->assertSame('sms', $challenge->tan_channel);
        $this->assertSame('***1234', $challenge->tan_info);
    }

    public function testCreateFromArray(): void
    {
        $data = [
            'challenge_id' => 'ch-456',
            'tan_channel' => TanChannel::EMAIL,
            'tan_info' => 'u***@example.com',
        ];

        $challenge = Challenge::createFromArray($data);

        $this->assertSame('ch-456', $challenge->challenge_id);
        $this->assertSame('email', $challenge->tan_channel);
        $this->assertSame('u***@example.com', $challenge->tan_info);
    }
}



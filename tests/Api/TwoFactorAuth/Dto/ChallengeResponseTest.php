<?php

namespace Taler\Tests\Api\TwoFactorAuth\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\TwoFactorAuth\Dto\Challenge;
use Taler\Api\TwoFactorAuth\Dto\ChallengeResponse;
use Taler\Api\TwoFactorAuth\Dto\TanChannel;

final class ChallengeResponseTest extends TestCase
{
    public function testConstructor(): void
    {
        $challenges = [
            new Challenge('ch-1', TanChannel::SMS, '***1234'),
            new Challenge('ch-2', TanChannel::EMAIL, 'u***@example.com'),
        ];

        $response = new ChallengeResponse(
            challenges: $challenges,
            combi_and: true
        );

        $this->assertCount(2, $response->challenges);
        $this->assertTrue($response->combi_and);

        $this->assertSame('ch-1', $response->challenges[0]->challenge_id);
        $this->assertSame('sms', $response->challenges[0]->tan_channel);
        $this->assertSame('***1234', $response->challenges[0]->tan_info);

        $this->assertSame('ch-2', $response->challenges[1]->challenge_id);
        $this->assertSame('email', $response->challenges[1]->tan_channel);
        $this->assertSame('u***@example.com', $response->challenges[1]->tan_info);
    }

    public function testCreateFromArray(): void
    {
        $data = [
            'challenges' => [
                [
                    'challenge_id' => 'ch-1',
                    'tan_channel' => TanChannel::SMS,
                    'tan_info' => '***1234',
                ],
                [
                    'challenge_id' => 'ch-2',
                    'tan_channel' => TanChannel::EMAIL,
                    'tan_info' => 'u***@example.com',
                ],
            ],
            'combi_and' => false,
        ];

        $response = ChallengeResponse::createFromArray($data);

        $this->assertCount(2, $response->challenges);
        $this->assertFalse($response->combi_and);
        $this->assertSame('ch-1', $response->challenges[0]->challenge_id);
        $this->assertSame('sms', $response->challenges[0]->tan_channel);
        $this->assertSame('***1234', $response->challenges[0]->tan_info);
        $this->assertSame('ch-2', $response->challenges[1]->challenge_id);
        $this->assertSame('email', $response->challenges[1]->tan_channel);
        $this->assertSame('u***@example.com', $response->challenges[1]->tan_info);
    }
}



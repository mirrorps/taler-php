<?php

namespace Taler\Tests\Api\TwoFactorAuth\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\Timestamp;
use Taler\Api\TwoFactorAuth\Dto\ChallengeRequestResponse;

final class ChallengeRequestResponseTest extends TestCase
{
    public function testConstructor(): void
    {
        $dto = new ChallengeRequestResponse(
            solve_expiration: new Timestamp(1700001234),
            earliest_retransmission: new Timestamp(1700002234)
        );

        $this->assertInstanceOf(Timestamp::class, $dto->solve_expiration);
        $this->assertInstanceOf(Timestamp::class, $dto->earliest_retransmission);
        $this->assertSame(1700001234, $dto->solve_expiration->t_s);
        $this->assertSame(1700002234, $dto->earliest_retransmission->t_s);
    }

    public function testCreateFromArray(): void
    {
        $data = [
            'solve_expiration' => ['t_s' => 1700010000],
            'earliest_retransmission' => ['t_s' => 1700011000],
        ];

        $dto = ChallengeRequestResponse::createFromArray($data);

        $this->assertInstanceOf(Timestamp::class, $dto->solve_expiration);
        $this->assertInstanceOf(Timestamp::class, $dto->earliest_retransmission);
        $this->assertSame(1700010000, $dto->solve_expiration->t_s);
        $this->assertSame(1700011000, $dto->earliest_retransmission->t_s);
    }
}




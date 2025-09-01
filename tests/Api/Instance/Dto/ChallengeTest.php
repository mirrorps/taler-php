<?php

namespace Taler\Tests\Api\Instance\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Instance\Dto\Challenge;

/**
 * Test cases for Challenge DTO.
 *
 * @since v21
 */
class ChallengeTest extends TestCase
{
    /**
     * Test creating a Challenge from array.
     */
    public function testCreateFromArray(): void
    {
        $data = [
            'challenge_id' => 'challenge-123'
        ];

        $challenge = Challenge::createFromArray($data);

        $this->assertInstanceOf(Challenge::class, $challenge);
        $this->assertEquals('challenge-123', $challenge->getChallengeId());
    }

    /**
     * Test Challenge constructor.
     */
    public function testConstructor(): void
    {
        $challenge = new Challenge(
            challenge_id: 'challenge-789'
        );

        $this->assertEquals('challenge-789', $challenge->getChallengeId());
    }
}

<?php

namespace Taler\Tests\Api\TwoFactorAuth\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\TwoFactorAuth\Dto\TanChannel;

final class TanChannelTest extends TestCase
{
    public function testConstants(): void
    {
        $this->assertSame('sms', TanChannel::SMS);
        $this->assertSame('email', TanChannel::EMAIL);
    }
}



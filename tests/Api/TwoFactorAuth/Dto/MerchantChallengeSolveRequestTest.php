<?php

namespace Taler\Tests\Api\TwoFactorAuth\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\TwoFactorAuth\Dto\MerchantChallengeSolveRequest;

final class MerchantChallengeSolveRequestTest extends TestCase
{
    public function testConstructorAndValidation(): void
    {
        $req = new MerchantChallengeSolveRequest(tan: '123456');
        $this->assertSame('123456', $req->tan);
    }

    public function testCreateFromArray(): void
    {
        $req = MerchantChallengeSolveRequest::createFromArray(['tan' => 'abcdef']);
        $this->assertSame('abcdef', $req->tan);
    }

    public function testValidationFailsOnEmptyTan(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new MerchantChallengeSolveRequest(tan: '');
    }
}




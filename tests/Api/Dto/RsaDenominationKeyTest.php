<?php

namespace Taler\Tests\Api\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\RsaDenominationKey;

class RsaDenominationKeyTest extends TestCase
{
    public function testCreation(): void
    {
        $ageMask = 123;
        $rsaPub = 'test-rsa-pub-key';
        
        $key = new RsaDenominationKey($ageMask, $rsaPub);
        
        $this->assertEquals('RSA', $key->getCipher());
        $this->assertEquals($ageMask, $key->getAgeMask());
        $this->assertEquals($rsaPub, $key->getRsaPub());
    }

    public function testCreateFromArray(): void
    {
        $data = [
            'age_mask' => 123,
            'rsa_pub' => 'test-rsa-pub-key'
        ];
        
        $key = RsaDenominationKey::createFromArray($data);
        
        $this->assertEquals('RSA', $key->getCipher());
        $this->assertEquals($data['age_mask'], $key->getAgeMask());
        $this->assertEquals($data['rsa_pub'], $key->getRsaPub());
    }
} 
<?php

namespace Taler\Tests\Api\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\CsDenominationKey;

class CsDenominationKeyTest extends TestCase
{
    public function testCreation(): void
    {
        $ageMask = 123;
        $csPub = 'test-cs-pub-key';
        
        $key = new CsDenominationKey($ageMask, $csPub);
        
        $this->assertEquals('CS', $key->getCipher());
        $this->assertEquals($ageMask, $key->getAgeMask());
        $this->assertEquals($csPub, $key->getCsPub());
    }

    public function testCreateFromArray(): void
    {
        $data = [
            'age_mask' => 123,
            'cs_pub' => 'test-cs-pub-key'
        ];
        
        $key = CsDenominationKey::createFromArray($data);
        
        $this->assertEquals('CS', $key->getCipher());
        $this->assertEquals($data['age_mask'], $key->getAgeMask());
        $this->assertEquals($data['cs_pub'], $key->getCsPub());
    }
} 
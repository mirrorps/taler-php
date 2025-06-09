<?php

namespace Taler\Tests\Api\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\SignKey;

class SignKeyTest extends TestCase
{
    private const SAMPLE_KEY = 'sample_key';
    private const SAMPLE_STAMP_START = '2024-01-01T00:00:00Z';
    private const SAMPLE_STAMP_EXPIRE = '2024-12-31T23:59:59Z';
    private const SAMPLE_STAMP_END = '2025-12-31T23:59:59Z';
    private const SAMPLE_MASTER_SIG = 'sample_master_sig';

    public function testConstructorWithValidData(): void
    {
        $dto = new SignKey(
            self::SAMPLE_KEY,
            self::SAMPLE_STAMP_START,
            self::SAMPLE_STAMP_EXPIRE,
            self::SAMPLE_STAMP_END,
            self::SAMPLE_MASTER_SIG
        );

        $this->assertEquals(self::SAMPLE_KEY, $dto->key);
        $this->assertEquals(self::SAMPLE_STAMP_START, $dto->stamp_start);
        $this->assertEquals(self::SAMPLE_STAMP_EXPIRE, $dto->stamp_expire);
        $this->assertEquals(self::SAMPLE_STAMP_END, $dto->stamp_end);
        $this->assertEquals(self::SAMPLE_MASTER_SIG, $dto->master_sig);
    }

    public function testFromArrayWithValidData(): void
    {
        $data = [
            'key' => self::SAMPLE_KEY,
            'stamp_start' => self::SAMPLE_STAMP_START,
            'stamp_expire' => self::SAMPLE_STAMP_EXPIRE,
            'stamp_end' => self::SAMPLE_STAMP_END,
            'master_sig' => self::SAMPLE_MASTER_SIG,
        ];

        $dto = SignKey::fromArray($data);

        $this->assertEquals(self::SAMPLE_KEY, $dto->key);
        $this->assertEquals(self::SAMPLE_STAMP_START, $dto->stamp_start);
        $this->assertEquals(self::SAMPLE_STAMP_EXPIRE, $dto->stamp_expire);
        $this->assertEquals(self::SAMPLE_STAMP_END, $dto->stamp_end);
        $this->assertEquals(self::SAMPLE_MASTER_SIG, $dto->master_sig);
    }
} 
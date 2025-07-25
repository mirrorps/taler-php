<?php

namespace Taler\Tests\Api\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\SignKey;
use Taler\Api\Dto\Timestamp;

class SignKeyTest extends TestCase
{
    private const SAMPLE_KEY = 'sample_key';
    private const SAMPLE_STAMP_START_S = 1704067200; // 2024-01-01T00:00:00Z in seconds
    private const SAMPLE_STAMP_EXPIRE_S = 1735689599; // 2024-12-31T23:59:59Z in seconds
    private const SAMPLE_STAMP_END_S = 1767225599; // 2025-12-31T23:59:59Z in seconds
    private const SAMPLE_MASTER_SIG = 'sample_master_sig';

    public function testConstructorWithValidData(): void
    {
        $stampStart = new Timestamp(self::SAMPLE_STAMP_START_S);
        $stampExpire = new Timestamp(self::SAMPLE_STAMP_EXPIRE_S);
        $stampEnd = new Timestamp(self::SAMPLE_STAMP_END_S);

        $dto = new SignKey(
            self::SAMPLE_KEY,
            $stampStart,
            $stampExpire,
            $stampEnd,
            self::SAMPLE_MASTER_SIG
        );

        $this->assertEquals(self::SAMPLE_KEY, $dto->key);
        $this->assertSame($stampStart, $dto->stamp_start);
        $this->assertSame($stampExpire, $dto->stamp_expire);
        $this->assertSame($stampEnd, $dto->stamp_end);
        $this->assertEquals(self::SAMPLE_MASTER_SIG, $dto->master_sig);
    }

    public function testFromArrayWithValidData(): void
    {
        $data = [
            'key' => self::SAMPLE_KEY,
            'stamp_start' => ['t_s' => self::SAMPLE_STAMP_START_S],
            'stamp_expire' => ['t_s' => self::SAMPLE_STAMP_EXPIRE_S],
            'stamp_end' => ['t_s' => self::SAMPLE_STAMP_END_S],
            'master_sig' => self::SAMPLE_MASTER_SIG,
        ];

        $dto = SignKey::fromArray($data);

        $this->assertEquals(self::SAMPLE_KEY, $dto->key);
        $this->assertInstanceOf(Timestamp::class, $dto->stamp_start);
        $this->assertSame(self::SAMPLE_STAMP_START_S, $dto->stamp_start->t_s);
        $this->assertInstanceOf(Timestamp::class, $dto->stamp_expire);
        $this->assertSame(self::SAMPLE_STAMP_EXPIRE_S, $dto->stamp_expire->t_s);
        $this->assertInstanceOf(Timestamp::class, $dto->stamp_end);
        $this->assertSame(self::SAMPLE_STAMP_END_S, $dto->stamp_end->t_s);
        $this->assertEquals(self::SAMPLE_MASTER_SIG, $dto->master_sig);
    }
} 
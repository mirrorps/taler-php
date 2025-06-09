<?php

namespace Taler\Tests\Api\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\ExtensionManifest;

class ExtensionManifestTest extends TestCase
{
    private const SAMPLE_VERSION = '1.0.0';

    public function testConstructorWithRequiredData(): void
    {
        $dto = new ExtensionManifest(
            true,
            self::SAMPLE_VERSION
        );

        $this->assertTrue($dto->critical);
        $this->assertEquals(self::SAMPLE_VERSION, $dto->version);
        $this->assertNull($dto->config);
    }

    public function testConstructorWithAllData(): void
    {
        $config = (object)['key' => 'value'];
        $dto = new ExtensionManifest(
            false,
            self::SAMPLE_VERSION,
            $config
        );

        $this->assertFalse($dto->critical);
        $this->assertEquals(self::SAMPLE_VERSION, $dto->version);
        $this->assertEquals($config, $dto->config);
    }

    public function testFromArrayWithRequiredData(): void
    {
        $data = [
            'critical' => true,
            'version' => self::SAMPLE_VERSION,
        ];

        $dto = ExtensionManifest::fromArray($data);

        $this->assertTrue($dto->critical);
        $this->assertEquals(self::SAMPLE_VERSION, $dto->version);
        $this->assertNull($dto->config);
    }

    public function testFromArrayWithAllData(): void
    {
        $config = (object)['key' => 'value'];
        $data = [
            'critical' => false,
            'version' => self::SAMPLE_VERSION,
            'config' => $config,
        ];

        $dto = ExtensionManifest::fromArray($data);

        $this->assertFalse($dto->critical);
        $this->assertEquals(self::SAMPLE_VERSION, $dto->version);
        $this->assertEquals($config, $dto->config);
    }
} 
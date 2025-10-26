<?php

namespace Taler\Tests\Helpers;

use PHPUnit\Framework\TestCase;
use function Taler\Helpers\parseLibtoolVersion;
use function Taler\Helpers\isProtocolCompatible;
use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Monolog\Logger as MonoLogger;
use Monolog\Handler\TestHandler;
use Taler\Factory\Factory;
use Taler\Taler;

class VersioningTest extends TestCase
{
    public function testParseLibtoolVersion(): void
    {
        $this->assertSame([5,0,1], parseLibtoolVersion('5:0:1'));
        $this->assertSame([6,12,0], parseLibtoolVersion('6:12:0'));
        $this->assertNull(parseLibtoolVersion('6:12'));
        $this->assertNull(parseLibtoolVersion('a:b:c'));
        $this->assertNull(parseLibtoolVersion(''));
    }

    public function testIsProtocolCompatible(): void
    {
        // server current=5, age=1 -> supports client 5 and 4
        $this->assertTrue(isProtocolCompatible(5, 1, 5));
        $this->assertTrue(isProtocolCompatible(5, 1, 4));
        $this->assertFalse(isProtocolCompatible(5, 1, 3));

        // server current=6, age=2 -> supports 6,5,4
        $this->assertTrue(isProtocolCompatible(6, 2, 6));
        $this->assertTrue(isProtocolCompatible(6, 2, 5));
        $this->assertTrue(isProtocolCompatible(6, 2, 4));
        $this->assertFalse(isProtocolCompatible(6, 2, 3));
    }

    public function testFactoryLogsWarningWhenClientVersionOutOfRange(): void
    {
        $mockClient = new MockClient();
        $psr17 = new Psr17Factory();

        // Server supports only current=19 (age=0); client current is 20 -> out of range
        $payload = json_encode([
            'version' => '19:0:0',
            'name' => 'taler-merchant',
            'currency' => 'EUR',
            'currencies' => [
                'EUR' => [
                    'name' => 'Euro',
                    'currency' => 'EUR',
                    'num_fractional_input_digits' => 2,
                    'num_fractional_normal_digits' => 2,
                    'num_fractional_trailing_zero_digits' => 2,
                    'alt_unit_names' => ['0' => '€']
                ]
            ],
            'exchanges' => [],
            'have_self_provisioning' => false,
            'have_donau' => false
        ], JSON_THROW_ON_ERROR);

        $response = $psr17->createResponse(200)->withHeader('Content-Type', 'application/json');
        $response = $response->withBody($psr17->createStream($payload));
        $mockClient->addResponse($response);

        $testHandler = new TestHandler(MonoLogger::WARNING);
        $logger = new MonoLogger('test');
        $logger->pushHandler($testHandler);

        $taler = Factory::create([
            'base_url' => 'https://backend.demo.taler.net/instances/sandbox',
            'token' => 'Bearer test-token',
            'client' => $mockClient,
            'logger' => $logger,
        ]);

        $this->assertInstanceOf(Taler::class, $taler);
        $this->assertTrue($testHandler->hasWarningRecords());
    }
}



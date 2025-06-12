<?php

namespace Taler\Tests\Api\Exchange\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\Timestamp;
use Taler\Api\Exchange\Dto\TrackTransactionResponse;

class TrackTransactionResponseTest extends TestCase
{
    /** @var array{
     *     wtid: string,
     *     execution_time: array{t_s: int|string},
     *     coin_contribution: string,
     *     exchange_sig: string,
     *     exchange_pub: string
     * }
     */
    private array $validData = [
        'wtid' => 'AAAQEAYEAUDAOCAJBIFQYDIOB4',  // Example Base32 string
        'execution_time' => ['t_s' => 1710510600], // 2024-03-15T14:30:00Z
        'coin_contribution' => '10.50',
        'exchange_sig' => 'ED25519-SIG-ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        'exchange_pub' => 'ED25519-PUB-123456789ABCDEFGHIJKLMN'
    ];

    public function testConstructor(): void
    {
        $response = new TrackTransactionResponse(
            wtid: $this->validData['wtid'],
            execution_time: new Timestamp($this->validData['execution_time']['t_s']),
            coin_contribution: $this->validData['coin_contribution'],
            exchange_sig: $this->validData['exchange_sig'],
            exchange_pub: $this->validData['exchange_pub']
        );

        $this->assertSame($this->validData['wtid'], $response->wtid);
        $this->assertSame($this->validData['execution_time']['t_s'], $response->execution_time->t_s);
        $this->assertSame($this->validData['coin_contribution'], $response->coin_contribution);
        $this->assertSame($this->validData['exchange_sig'], $response->exchange_sig);
        $this->assertSame($this->validData['exchange_pub'], $response->exchange_pub);
    }

    public function testFromArray(): void
    {
        $response = TrackTransactionResponse::fromArray($this->validData);

        $this->assertSame($this->validData['wtid'], $response->wtid);
        $this->assertSame($this->validData['execution_time']['t_s'], $response->execution_time->t_s);
        $this->assertSame($this->validData['coin_contribution'], $response->coin_contribution);
        $this->assertSame($this->validData['exchange_sig'], $response->exchange_sig);
        $this->assertSame($this->validData['exchange_pub'], $response->exchange_pub);
    }

    public function testFromArrayWithDifferentData(): void
    {
        /** @var array{
         *     wtid: string,
         *     execution_time: array{t_s: int|string},
         *     coin_contribution: string,
         *     exchange_sig: string,
         *     exchange_pub: string
         * } $data
         */
        $data = [
            'wtid' => 'IFAYDKNBYGRRCFIQCAIBAEAQCA',  // Different Base32 string
            'execution_time' => ['t_s' => 1710576300], // 2024-03-16T09:45:00Z
            'coin_contribution' => '25.75',
            'exchange_sig' => 'ED25519-SIG-ZYXWVUTSRQPONMLKJIHGFEDCBA',
            'exchange_pub' => 'ED25519-PUB-NMLKJIHGFEDCBA987654321'
        ];

        $response = TrackTransactionResponse::fromArray($data);

        $this->assertSame($data['wtid'], $response->wtid);
        $this->assertSame($data['execution_time']['t_s'], $response->execution_time->t_s);
        $this->assertSame($data['coin_contribution'], $response->coin_contribution);
        $this->assertSame($data['exchange_sig'], $response->exchange_sig);
        $this->assertSame($data['exchange_pub'], $response->exchange_pub);
    }

    public function testObjectImmutability(): void
    {
        $response = TrackTransactionResponse::fromArray($this->validData);

        // Verify that all properties are readonly
        $this->assertTrue((new \ReflectionProperty($response, 'wtid'))->isReadOnly());
        $this->assertTrue((new \ReflectionProperty($response, 'execution_time'))->isReadOnly());
        $this->assertTrue((new \ReflectionProperty($response, 'coin_contribution'))->isReadOnly());
        $this->assertTrue((new \ReflectionProperty($response, 'exchange_sig'))->isReadOnly());
        $this->assertTrue((new \ReflectionProperty($response, 'exchange_pub'))->isReadOnly());
    }
} 
<?php

namespace Taler\Tests\Api\Exchange\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\Timestamp;
use Taler\Api\Exchange\Dto\TrackTransactionAcceptedResponse;

class TrackTransactionAcceptedResponseTest extends TestCase
{
    /** @var array{
     *     requirement_row: int,
     *     kyc_ok: bool,
     *     execution_time: array{t_s: int|string},
     *     account_pub: string
     * }
     */
    private array $fullData = [
        'requirement_row' => 42,
        'kyc_ok' => true,
        'execution_time' => ['t_s' => 1710510600],
        'account_pub' => 'ABCDEF123456'
    ];

    public function testConstructorWithAllParameters(): void
    {
        $response = new TrackTransactionAcceptedResponse(
            requirement_row: $this->fullData['requirement_row'],
            kyc_ok: $this->fullData['kyc_ok'],
            execution_time: new Timestamp($this->fullData['execution_time']['t_s']),
            account_pub: $this->fullData['account_pub']
        );

        $this->assertSame($this->fullData['requirement_row'], $response->requirement_row);
        $this->assertSame($this->fullData['kyc_ok'], $response->kyc_ok);
        $this->assertSame($this->fullData['execution_time']['t_s'], $response->execution_time->t_s);
        $this->assertSame($this->fullData['account_pub'], $response->account_pub);
    }

    public function testConstructorWithRequiredOnly(): void
    {
        $response = new TrackTransactionAcceptedResponse(
            requirement_row: null,
            kyc_ok: $this->fullData['kyc_ok'],
            execution_time: new Timestamp($this->fullData['execution_time']['t_s']),
            account_pub: null
        );

        $this->assertNull($response->requirement_row);
        $this->assertSame($this->fullData['kyc_ok'], $response->kyc_ok);
        $this->assertSame($this->fullData['execution_time']['t_s'], $response->execution_time->t_s);
        $this->assertNull($response->account_pub);
    }

    public function testFromArrayWithAllParameters(): void
    {
        $response = TrackTransactionAcceptedResponse::fromArray($this->fullData);

        $this->assertSame($this->fullData['requirement_row'], $response->requirement_row);
        $this->assertSame($this->fullData['kyc_ok'], $response->kyc_ok);
        $this->assertSame($this->fullData['execution_time']['t_s'], $response->execution_time->t_s);
        $this->assertSame($this->fullData['account_pub'], $response->account_pub);
    }

    public function testFromArrayWithRequiredOnly(): void
    {
        /** @var array{
         *     kyc_ok: bool,
         *     execution_time: array{t_s: int|string}
         * } $data
         */
        $data = [
            'kyc_ok' => false,
            'execution_time' => ['t_s' => 1710510600]
        ];

        $response = TrackTransactionAcceptedResponse::fromArray($data);

        $this->assertNull($response->requirement_row);
        $this->assertSame($data['kyc_ok'], $response->kyc_ok);
        $this->assertSame($data['execution_time']['t_s'], $response->execution_time->t_s);
        $this->assertNull($response->account_pub);
    }

    public function testFromArrayWithNullableFields(): void
    {
        /** @var array{
         *     requirement_row: null,
         *     kyc_ok: bool,
         *     execution_time: array{t_s: int|string},
         *     account_pub: null
         * } $data
         */
        $data = [
            'requirement_row' => null,
            'kyc_ok' => true,
            'execution_time' => ['t_s' => 1710510600],
            'account_pub' => null
        ];

        $response = TrackTransactionAcceptedResponse::fromArray($data);

        $this->assertNull($response->requirement_row);
        $this->assertTrue($response->kyc_ok);
        $this->assertSame($data['execution_time']['t_s'], $response->execution_time->t_s);
        $this->assertNull($response->account_pub);
    }
} 
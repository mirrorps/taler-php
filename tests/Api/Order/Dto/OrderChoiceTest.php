<?php

namespace Taler\Tests\Api\Order\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Order\Dto\OrderChoice;
use Taler\Api\Order\Dto\OrderInputToken;
use Taler\Api\Order\Dto\OrderOutputToken;
use Taler\Api\Order\Dto\OrderOutputTaxReceipt;
use Taler\Api\Dto\Timestamp;

class OrderChoiceTest extends TestCase
{
    private const SAMPLE_AMOUNT = 'EUR:10.00';
    private const SAMPLE_MAX_FEE = 'EUR:1.00';
    private const SAMPLE_TOKEN_FAMILY_SLUG = 'test-token-family';
    private const SAMPLE_TIMESTAMP = 1234567890;

    public function testConstruct(): void
    {
        $orderChoice = new OrderChoice(
            amount: self::SAMPLE_AMOUNT
        );

        $this->assertSame(self::SAMPLE_AMOUNT, $orderChoice->amount);
        $this->assertEmpty($orderChoice->inputs);
        $this->assertEmpty($orderChoice->outputs);
        $this->assertNull($orderChoice->max_fee);
    }

    public function testConstructWithAllParameters(): void
    {
        $input = new OrderInputToken(self::SAMPLE_TOKEN_FAMILY_SLUG);
        $output = new OrderOutputToken(self::SAMPLE_TOKEN_FAMILY_SLUG);
        $taxReceipt = new OrderOutputTaxReceipt();

        $orderChoice = new OrderChoice(
            amount: self::SAMPLE_AMOUNT,
            inputs: [$input],
            outputs: [$output, $taxReceipt],
            max_fee: self::SAMPLE_MAX_FEE
        );

        $this->assertSame(self::SAMPLE_AMOUNT, $orderChoice->amount);
        $this->assertCount(1, $orderChoice->inputs);
        $this->assertCount(2, $orderChoice->outputs);
        $this->assertSame(self::SAMPLE_MAX_FEE, $orderChoice->max_fee);
        $this->assertInstanceOf(OrderInputToken::class, $orderChoice->inputs[0]);
        $this->assertInstanceOf(OrderOutputToken::class, $orderChoice->outputs[0]);
        $this->assertInstanceOf(OrderOutputTaxReceipt::class, $orderChoice->outputs[1]);
    }

    public function testConstructWithoutValidation(): void
    {
        $orderChoice = new OrderChoice(
            amount: self::SAMPLE_AMOUNT,
            validate: false
        );

        $this->assertSame(self::SAMPLE_AMOUNT, $orderChoice->amount);
    }

    public function testCreateFromArrayWithRequiredParameters(): void
    {
        $data = [
            'amount' => self::SAMPLE_AMOUNT
        ];

        $orderChoice = OrderChoice::createFromArray($data);

        $this->assertSame(self::SAMPLE_AMOUNT, $orderChoice->amount);
        $this->assertEmpty($orderChoice->inputs);
        $this->assertEmpty($orderChoice->outputs);
        $this->assertNull($orderChoice->max_fee);
    }

    public function testCreateFromArrayWithAllParameters(): void
    {
        $data = [
            'amount' => self::SAMPLE_AMOUNT,
            'inputs' => [
                [
                    'token_family_slug' => self::SAMPLE_TOKEN_FAMILY_SLUG
                ]
            ],
            'outputs' => [
                [
                    'type' => 'token',
                    'token_family_slug' => self::SAMPLE_TOKEN_FAMILY_SLUG,
                    'valid_at' => ['t_s' => self::SAMPLE_TIMESTAMP]
                ],
                [
                    'type' => 'tax-receipt'
                ]
            ],
            'max_fee' => self::SAMPLE_MAX_FEE
        ];

        $orderChoice = OrderChoice::createFromArray($data);

        $this->assertSame(self::SAMPLE_AMOUNT, $orderChoice->amount);
        $this->assertCount(1, $orderChoice->inputs);
        $this->assertCount(2, $orderChoice->outputs);
        $this->assertSame(self::SAMPLE_MAX_FEE, $orderChoice->max_fee);
        $this->assertInstanceOf(OrderInputToken::class, $orderChoice->inputs[0]);
        $this->assertInstanceOf(OrderOutputToken::class, $orderChoice->outputs[0]);
        $this->assertInstanceOf(OrderOutputTaxReceipt::class, $orderChoice->outputs[1]);
    }

    public function testValidationFailsWithEmptyAmount(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount cannot be empty');

        new OrderChoice(amount: '');
    }

    public function testValidationFailsWithInvalidAmountFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount must be a valid Taler amount in the format CURRENCY:VALUE');

        new OrderChoice(amount: '42');
    }

    public function testValidationFailsWithInvalidMaxFeeFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Max fee must be a valid Taler amount in the format CURRENCY:VALUE');

        new OrderChoice(
            amount: self::SAMPLE_AMOUNT,
            max_fee: '1.00'
        );
    }

    public function testValidationFailsWithInvalidInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Each input must be an instance of OrderInputToken');

        $inputs = [null];
        new OrderChoice(
            amount: self::SAMPLE_AMOUNT,
            inputs: $inputs /** @phpstan-ignore-line */
        );
    }

    public function testValidationFailsWithInvalidOutput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Each output must be an instance of OrderOutputToken or OrderOutputTaxReceipt');

        $outputs = [null];
        new OrderChoice(
            amount: self::SAMPLE_AMOUNT,
            outputs: $outputs /** @phpstan-ignore-line */
        );
    }
}
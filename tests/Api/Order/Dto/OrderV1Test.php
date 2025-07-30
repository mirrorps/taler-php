<?php

namespace Taler\Tests\Api\Order\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Order\Dto\OrderV1;
use Taler\Api\Order\Dto\OrderChoice;
use Taler\Api\Order\Dto\OrderInputToken;
use Taler\Api\Order\Dto\OrderOutputToken;
use Taler\Api\Order\Dto\OrderOutputTaxReceipt;

class OrderV1Test extends TestCase
{
    private const SAMPLE_AMOUNT = '10.00';
    private const SAMPLE_MAX_FEE = '1.00';
    private const SAMPLE_TOKEN_FAMILY_SLUG = 'test-token-family';

    public function testConstruct(): void
    {
        $orderV1 = new OrderV1();

        $this->assertNull($orderV1->choices);
        $this->assertSame(1, $orderV1->getVersion());
    }

    public function testConstructWithChoices(): void
    {
        $choice = new OrderChoice(amount: self::SAMPLE_AMOUNT);
        $orderV1 = new OrderV1(choices: [$choice]);

        $this->assertCount(1, $orderV1->choices);
        $this->assertInstanceOf(OrderChoice::class, $orderV1->choices[0]);
        $this->assertSame(self::SAMPLE_AMOUNT, $orderV1->choices[0]->amount);
        $this->assertSame(1, $orderV1->getVersion());
    }

    public function testConstructWithoutValidation(): void
    {
        $choice = new OrderChoice(amount: self::SAMPLE_AMOUNT);
        $orderV1 = new OrderV1(
            choices: [$choice],
            validate: false
        );

        $this->assertCount(1, $orderV1->choices);
        $this->assertInstanceOf(OrderChoice::class, $orderV1->choices[0]);
    }

    public function testCreateFromArrayWithoutChoices(): void
    {
        $data = [
            'version' => 1
        ];

        $orderV1 = OrderV1::createFromArray($data);

        $this->assertNull($orderV1->choices);
        $this->assertSame(1, $orderV1->getVersion());
    }

    public function testCreateFromArrayWithChoices(): void
    {
        $data = [
            'version' => 1,
            'choices' => [
                [
                    'amount' => self::SAMPLE_AMOUNT,
                    'inputs' => [
                        [
                            'token_family_slug' => self::SAMPLE_TOKEN_FAMILY_SLUG
                        ]
                    ],
                    'outputs' => [
                        [
                            'type' => 'token',
                            'token_family_slug' => self::SAMPLE_TOKEN_FAMILY_SLUG
                        ],
                        [
                            'type' => 'tax-receipt'
                        ]
                    ],
                    'max_fee' => self::SAMPLE_MAX_FEE
                ]
            ]
        ];

        $orderV1 = OrderV1::createFromArray($data);

        $this->assertCount(1, $orderV1->choices);
        $this->assertInstanceOf(OrderChoice::class, $orderV1->choices[0]);
        $this->assertSame(self::SAMPLE_AMOUNT, $orderV1->choices[0]->amount);
        $this->assertCount(1, $orderV1->choices[0]->inputs);
        $this->assertCount(2, $orderV1->choices[0]->outputs);
        $this->assertSame(self::SAMPLE_MAX_FEE, $orderV1->choices[0]->max_fee);
        $this->assertInstanceOf(OrderInputToken::class, $orderV1->choices[0]->inputs[0]);
        $this->assertInstanceOf(OrderOutputToken::class, $orderV1->choices[0]->outputs[0]);
        $this->assertInstanceOf(OrderOutputTaxReceipt::class, $orderV1->choices[0]->outputs[1]);
    }

    public function testValidationFailsWithInvalidChoice(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Each choice must be an instance of OrderChoice');

        new OrderV1(choices: [new \stdClass()]); /** @phpstan-ignore-line */
    }

    public function testCreateFromArrayFailsWithInvalidVersion(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Version must be 1');

        OrderV1::createFromArray(['version' => 0]);
    }
}
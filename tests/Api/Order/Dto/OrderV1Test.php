<?php

namespace Taler\Tests\Api\Order\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Order\Dto\OrderV1;
use Taler\Api\Order\Dto\OrderChoice;
use Taler\Api\Order\Dto\OrderInputToken;
use Taler\Api\Order\Dto\OrderOutputTaxReceipt;
use Taler\Api\Order\Dto\OrderOutputToken;
use Taler\Api\Dto\Timestamp;
use Taler\Api\Dto\RelativeTime;
use Taler\Api\Dto\Location;
use Taler\Api\Inventory\Dto\Product;

class OrderV1Test extends TestCase
{
    public function testConstructMinimal(): void
    {
        $dto = new OrderV1(summary: 'Test order');

        $this->assertSame('Test order', $dto->summary);
        $this->assertNull($dto->choices);
    }

    public function testConstructWithoutValidation(): void
    {
        $dto = new OrderV1(summary: '', validate: false);

        $this->assertSame('', $dto->summary);
    }

    public function testCreateFromArrayMinimal(): void
    {
        $data = [
            'summary' => 'Test order'
        ];

        $dto = OrderV1::createFromArray($data);

        $this->assertInstanceOf(OrderV1::class, $dto);
        $this->assertSame('Test order', $dto->summary);
        $this->assertNull($dto->choices);
    }

    public function testCreateFromArrayFull(): void
    {
        $data = [
            'summary' => 'Full order',
            'choices' => [
                [
                    'amount' => '10.00',
                    'inputs' => [
                        ['n' => 0, 'token_family_slug' => 'family-1']
                    ],
                    'outputs' => [
                        ['type' => 'token', 'token_family_slug' => 'family-1'],
                        ['type' => 'tax-receipt']
                    ],
                    'max_fee' => '0.10'
                ]
            ],
            'summary_i18n' => ['en' => 'Full order'],
            'order_id' => 'ORDER_1',
            'public_reorder_url' => 'https://merchant.example/again',
            'fulfillment_url' => 'https://merchant.example/ok',
            'fulfillment_message' => 'done',
            'fulfillment_message_i18n' => ['en' => 'done'],
            'minimum_age' => 18,
            'products' => [
                ['description' => 'Item A']
            ],
            'timestamp' => ['t_s' => 123],
            'refund_deadline' => ['t_s' => 124],
            'pay_deadline' => ['t_s' => 125],
            'wire_transfer_deadline' => ['t_s' => 126],
            'merchant_base_url' => 'https://merchant.example/',
            'delivery_location' => ['country' => 'CH'],
            'delivery_date' => ['t_s' => 127],
            'auto_refund' => ['d_us' => 1000],
            'extra' => (object) ['k' => 'v']
        ];

        $dto = OrderV1::createFromArray($data);

        $this->assertSame('Full order', $dto->summary);
        $this->assertCount(1, $dto->choices);
        $this->assertInstanceOf(OrderChoice::class, $dto->choices[0]);
        $this->assertCount(1, $dto->choices[0]->inputs);
        $this->assertInstanceOf(OrderInputToken::class, $dto->choices[0]->inputs[0]);
        $this->assertCount(2, $dto->choices[0]->outputs);
        $this->assertInstanceOf(OrderOutputToken::class, $dto->choices[0]->outputs[0]);
        $this->assertInstanceOf(OrderOutputTaxReceipt::class, $dto->choices[0]->outputs[1]);

        $this->assertSame(['en' => 'Full order'], $dto->summary_i18n);
        $this->assertSame('ORDER_1', $dto->order_id);
        $this->assertSame('https://merchant.example/again', $dto->public_reorder_url);
        $this->assertSame('https://merchant.example/ok', $dto->fulfillment_url);
        $this->assertSame('done', $dto->fulfillment_message);
        $this->assertSame(['en' => 'done'], $dto->fulfillment_message_i18n);
        $this->assertSame(18, $dto->minimum_age);
        $this->assertCount(1, $dto->products);
        $this->assertInstanceOf(Product::class, $dto->products[0]);
        $this->assertInstanceOf(Timestamp::class, $dto->timestamp);
        $this->assertInstanceOf(Timestamp::class, $dto->refund_deadline);
        $this->assertInstanceOf(Timestamp::class, $dto->pay_deadline);
        $this->assertInstanceOf(Timestamp::class, $dto->wire_transfer_deadline);
        $this->assertSame('https://merchant.example/', $dto->merchant_base_url);
        $this->assertInstanceOf(Location::class, $dto->delivery_location);
        $this->assertInstanceOf(Timestamp::class, $dto->delivery_date);
        $this->assertInstanceOf(RelativeTime::class, $dto->auto_refund);
        $this->assertIsObject($dto->extra);
    }

    public function testValidationFailsOnEmptySummary(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new OrderV1(summary: '');
    }
}



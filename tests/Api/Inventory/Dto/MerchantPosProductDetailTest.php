<?php

namespace Taler\Tests\Api\Inventory\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Inventory\Dto\MerchantPosProductDetail;

class MerchantPosProductDetailTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = [
            'product_serial' => 5,
            'product_id' => 'p5',
            'product_name' => 'Soda',
            'categories' => [1, 2],
            'description' => 'Carbonated drink',
            'description_i18n' => ['en' => 'Carbonated drink'],
            'unit' => 'bottle',
            'price' => 'EUR:1.20',
            'image' => 'data:image/png;base64,AAAA',
            'total_stock' => 100,
            'minimum_age' => 0,
        ];

        $p = MerchantPosProductDetail::createFromArray($data);

        $this->assertSame(5, $p->product_serial);
        $this->assertSame('p5', $p->product_id);
        $this->assertSame('Soda', $p->product_name);
        $this->assertSame('bottle', $p->unit);
        $this->assertSame('EUR:1.20', $p->price);
        $this->assertSame(100, $p->total_stock);
    }
}



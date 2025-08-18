<?php

namespace Taler\Tests\Api\TokenFamilies\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\Timestamp;
use Taler\Api\TokenFamilies\Dto\TokenFamiliesList;
use Taler\Api\TokenFamilies\Dto\TokenFamilySummary;

class TokenFamiliesListTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $data = [
            'token_families' => [
                [
                    'slug' => 'fam-1',
                    'name' => 'Fam 1',
                    'valid_after' => ['t_s' => 1700000000],
                    'valid_before' => ['t_s' => 1800000000],
                    'kind' => 'discount',
                ],
                [
                    'slug' => 'fam-2',
                    'name' => 'Fam 2',
                    'valid_after' => ['t_s' => 1700000001],
                    'valid_before' => ['t_s' => 1800000001],
                    'kind' => 'subscription',
                ],
            ],
        ];

        $dto = TokenFamiliesList::createFromArray($data);

        $this->assertCount(2, $dto->token_families);
        $this->assertInstanceOf(TokenFamilySummary::class, $dto->token_families[0]);
        $this->assertSame('fam-1', $dto->token_families[0]->slug);
        $this->assertSame('discount', $dto->token_families[0]->kind);
        $this->assertInstanceOf(Timestamp::class, $dto->token_families[0]->valid_after);
        $this->assertInstanceOf(Timestamp::class, $dto->token_families[0]->valid_before);
    }
}



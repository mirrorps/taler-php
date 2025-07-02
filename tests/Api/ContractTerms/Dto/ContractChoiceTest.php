<?php

namespace Taler\Tests\Api\ContractTerms\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\ContractTerms\Dto\ContractChoice;
use Taler\Api\ContractTerms\Dto\ContractInputToken;
use Taler\Api\ContractTerms\Dto\ContractOutputToken;
use Taler\Api\ContractTerms\Dto\ContractOutputTaxReceipt;

class ContractChoiceTest extends TestCase
{
    private const SAMPLE_AMOUNT = 'EUR:10';
    private const SAMPLE_MAX_FEE = 'EUR:0.5';
    private const SAMPLE_TOKEN_FAMILY_SLUG = 'test-token-family';
    private const SAMPLE_KEY_INDEX = 1;
    private const SAMPLE_COUNT = 2;
    private const SAMPLE_DONAU_URLS = ['https://donau1.example.com', 'https://donau2.example.com'];
    private const SAMPLE_TAX_AMOUNT = 'EUR:2';

    public function testConstruct(): void
    {
        $inputs = [
            new ContractInputToken(
                token_family_slug: self::SAMPLE_TOKEN_FAMILY_SLUG,
                count: self::SAMPLE_COUNT
            )
        ];

        $outputs = [
            new ContractOutputToken(
                token_family_slug: self::SAMPLE_TOKEN_FAMILY_SLUG,
                key_index: self::SAMPLE_KEY_INDEX,
                count: self::SAMPLE_COUNT
            ),
            new ContractOutputTaxReceipt(
                donau_urls: self::SAMPLE_DONAU_URLS,
                amount: self::SAMPLE_TAX_AMOUNT
            )
        ];

        $contractChoice = new ContractChoice(
            amount: self::SAMPLE_AMOUNT,
            inputs: $inputs,
            outputs: $outputs,
            max_fee: self::SAMPLE_MAX_FEE
        );

        $this->assertSame(self::SAMPLE_AMOUNT, $contractChoice->amount);
        $this->assertSame($inputs, $contractChoice->inputs);
        $this->assertSame($outputs, $contractChoice->outputs);
        $this->assertSame(self::SAMPLE_MAX_FEE, $contractChoice->max_fee);
    }

    public function testCreateFromArray(): void
    {
        $data = [
            'amount' => self::SAMPLE_AMOUNT,
            'inputs' => [
                [
                    'token_family_slug' => self::SAMPLE_TOKEN_FAMILY_SLUG,
                    'count' => self::SAMPLE_COUNT
                ]
            ],
            'outputs' => [
                [
                    'token_family_slug' => self::SAMPLE_TOKEN_FAMILY_SLUG,
                    'key_index' => self::SAMPLE_KEY_INDEX,
                    'count' => self::SAMPLE_COUNT
                ],
                [
                    'donau_urls' => self::SAMPLE_DONAU_URLS,
                    'amount' => self::SAMPLE_TAX_AMOUNT
                ]
            ],
            'max_fee' => self::SAMPLE_MAX_FEE
        ];

        $contractChoice = ContractChoice::createFromArray($data);

        $this->assertSame(self::SAMPLE_AMOUNT, $contractChoice->amount);
        $this->assertCount(1, $contractChoice->inputs);
        $this->assertCount(2, $contractChoice->outputs);
        $this->assertSame(self::SAMPLE_MAX_FEE, $contractChoice->max_fee);

        $this->assertInstanceOf(ContractInputToken::class, $contractChoice->inputs[0]);
        $this->assertSame(self::SAMPLE_TOKEN_FAMILY_SLUG, $contractChoice->inputs[0]->token_family_slug);
        $this->assertSame(self::SAMPLE_COUNT, $contractChoice->inputs[0]->count);

        $this->assertInstanceOf(ContractOutputToken::class, $contractChoice->outputs[0]);
        $this->assertSame(self::SAMPLE_TOKEN_FAMILY_SLUG, $contractChoice->outputs[0]->token_family_slug);
        $this->assertSame(self::SAMPLE_KEY_INDEX, $contractChoice->outputs[0]->key_index);
        $this->assertSame(self::SAMPLE_COUNT, $contractChoice->outputs[0]->count);

        $this->assertInstanceOf(ContractOutputTaxReceipt::class, $contractChoice->outputs[1]);
        $this->assertSame(self::SAMPLE_DONAU_URLS, $contractChoice->outputs[1]->donau_urls);
        $this->assertSame(self::SAMPLE_TAX_AMOUNT, $contractChoice->outputs[1]->amount);
    }
} 
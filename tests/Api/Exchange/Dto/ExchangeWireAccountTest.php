<?php

namespace Taler\Tests\Api\Exchange\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Contract\AccountRestriction;
use Taler\Api\Dto\DenyAllAccountRestriction;
use Taler\Api\Dto\RegexAccountRestriction;
use Taler\Api\Exchange\Dto\ExchangeWireAccount;

class ExchangeWireAccountTest extends TestCase
{
    private const SAMPLE_PAYTO_URI = 'payto://iban/DEUTDEBB500/DE02100100100006820101';
    private const SAMPLE_MASTER_SIG = 'EDDSAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';
    private const SAMPLE_REGEX = '^payto://iban/[A-Z]{6}[0-9]{2}/DE';
    private const SAMPLE_HUMAN_HINT = 'Only German IBAN accounts are accepted';
    private const SAMPLE_CONVERSION_URL = 'https://exchange.demo/convert';
    private const SAMPLE_BANK_LABEL = 'Deutsche Bank';
    private const SAMPLE_PRIORITY = 10;

    /** @var array{
     *     payto_uri: string,
     *     master_sig: string,
     *     credit_restrictions: array<int, array{type: 'deny'}|array{
     *         type: 'regex',
     *         payto_regex: string,
     *         human_hint: string,
     *         human_hint_i18n?: array<string, string>|null
     *     }>,
     *     debit_restrictions: array<int, array{type: 'deny'}|array{
     *         type: 'regex',
     *         payto_regex: string,
     *         human_hint: string,
     *         human_hint_i18n?: array<string, string>|null
     *     }>,
     *     conversion_url: string,
     *     bank_label: string,
     *     priority: int
     * }
     */
    private array $validData;

    protected function setUp(): void
    {
        $this->validData = [
            'payto_uri' => self::SAMPLE_PAYTO_URI,
            'master_sig' => self::SAMPLE_MASTER_SIG,
            'credit_restrictions' => [
                [
                    'type' => 'regex',
                    'payto_regex' => self::SAMPLE_REGEX,
                    'human_hint' => self::SAMPLE_HUMAN_HINT
                ],
                [
                    'type' => 'deny'
                ]
            ],
            'debit_restrictions' => [
                [
                    'type' => 'regex',
                    'payto_regex' => self::SAMPLE_REGEX,
                    'human_hint' => self::SAMPLE_HUMAN_HINT,
                    'human_hint_i18n' => [
                        'en' => 'Only German IBAN accounts are accepted'
                    ]
                ]
            ],
            'conversion_url' => self::SAMPLE_CONVERSION_URL,
            'bank_label' => self::SAMPLE_BANK_LABEL,
            'priority' => self::SAMPLE_PRIORITY
        ];
    }

    public function testConstructWithValidData(): void
    {
        $creditRestrictions = [
            RegexAccountRestriction::fromArray([
                'type' => 'regex',
                'payto_regex' => self::SAMPLE_REGEX,
                'human_hint' => self::SAMPLE_HUMAN_HINT
            ]),
            DenyAllAccountRestriction::fromArray(['type' => 'deny'])
        ];

        $debitRestrictions = [
            RegexAccountRestriction::fromArray([
                'type' => 'regex',
                'payto_regex' => self::SAMPLE_REGEX,
                'human_hint' => self::SAMPLE_HUMAN_HINT,
                'human_hint_i18n' => [
                    'en' => 'Only German IBAN accounts are accepted'
                ]
            ])
        ];

        $account = new ExchangeWireAccount(
            payto_uri: self::SAMPLE_PAYTO_URI,
            master_sig: self::SAMPLE_MASTER_SIG,
            credit_restrictions: $creditRestrictions,
            debit_restrictions: $debitRestrictions,
            conversion_url: self::SAMPLE_CONVERSION_URL,
            bank_label: self::SAMPLE_BANK_LABEL,
            priority: self::SAMPLE_PRIORITY
        );

        $this->assertSame(self::SAMPLE_PAYTO_URI, $account->payto_uri);
        $this->assertSame(self::SAMPLE_MASTER_SIG, $account->master_sig);
        $this->assertEquals($creditRestrictions, $account->credit_restrictions);
        $this->assertEquals($debitRestrictions, $account->debit_restrictions);
        $this->assertSame(self::SAMPLE_CONVERSION_URL, $account->conversion_url);
        $this->assertSame(self::SAMPLE_BANK_LABEL, $account->bank_label);
        $this->assertSame(self::SAMPLE_PRIORITY, $account->priority);
    }

    public function testFromArrayWithValidData(): void
    {
        $account = ExchangeWireAccount::fromArray($this->validData);

        $this->assertSame(self::SAMPLE_PAYTO_URI, $account->payto_uri);
        $this->assertSame(self::SAMPLE_MASTER_SIG, $account->master_sig);
        $this->assertCount(2, $account->credit_restrictions);
        $this->assertCount(1, $account->debit_restrictions);

        // Test credit restrictions
        $this->assertInstanceOf(RegexAccountRestriction::class, $account->credit_restrictions[0]);
        $this->assertSame(self::SAMPLE_REGEX, $account->credit_restrictions[0]->payto_regex);
        $this->assertSame(self::SAMPLE_HUMAN_HINT, $account->credit_restrictions[0]->human_hint);
        $this->assertInstanceOf(DenyAllAccountRestriction::class, $account->credit_restrictions[1]);

        // Test debit restrictions
        $this->assertInstanceOf(RegexAccountRestriction::class, $account->debit_restrictions[0]);
        $this->assertSame(self::SAMPLE_REGEX, $account->debit_restrictions[0]->payto_regex);
        $this->assertSame(self::SAMPLE_HUMAN_HINT, $account->debit_restrictions[0]->human_hint);
        $this->assertSame(
            ['en' => 'Only German IBAN accounts are accepted'],
            $account->debit_restrictions[0]->human_hint_i18n
        );

        $this->assertSame(self::SAMPLE_CONVERSION_URL, $account->conversion_url);
        $this->assertSame(self::SAMPLE_BANK_LABEL, $account->bank_label);
        $this->assertSame(self::SAMPLE_PRIORITY, $account->priority);
    }

    public function testFromArrayWithMinimalData(): void
    {
        $minimalData = [
            'payto_uri' => self::SAMPLE_PAYTO_URI,
            'master_sig' => self::SAMPLE_MASTER_SIG
        ];

        $account = ExchangeWireAccount::fromArray($minimalData);

        $this->assertSame(self::SAMPLE_PAYTO_URI, $account->payto_uri);
        $this->assertSame(self::SAMPLE_MASTER_SIG, $account->master_sig);
        $this->assertEmpty($account->credit_restrictions);
        $this->assertEmpty($account->debit_restrictions);
        $this->assertNull($account->conversion_url);
        $this->assertNull($account->bank_label);
        $this->assertSame(0, $account->priority);
    }
} 
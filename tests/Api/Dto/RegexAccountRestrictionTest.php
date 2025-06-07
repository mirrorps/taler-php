<?php

namespace Taler\Tests\Api\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Dto\RegexAccountRestriction;

class RegexAccountRestrictionTest extends TestCase
{
    private const SAMPLE_PAYTO_REGEX = '^payto://sepa/([A-Z]{2}[0-9]{2}[A-Z0-9]{1,30})$';
    private const SAMPLE_HUMAN_HINT = 'Only SEPA accounts are allowed';

    /**
     * @var array<non-empty-string, non-empty-string>
     */
    private const SAMPLE_I18N = [
        'de' => 'Nur SEPA-Konten sind erlaubt',
        'fr' => 'Seuls les comptes SEPA sont autorisés',
        'es' => 'Solo se permiten cuentas SEPA'
    ];

    public function testConstructWithRequiredFields(): void
    {
        $restriction = new RegexAccountRestriction(
            payto_regex: self::SAMPLE_PAYTO_REGEX,
            human_hint: self::SAMPLE_HUMAN_HINT
        );

        $this->assertSame(self::SAMPLE_PAYTO_REGEX, $restriction->payto_regex);
        $this->assertSame(self::SAMPLE_HUMAN_HINT, $restriction->human_hint);
        $this->assertNull($restriction->human_hint_i18n);
        $this->assertSame('regex', $restriction->getType());
    }

    public function testConstructWithAllFields(): void
    {
        $restriction = new RegexAccountRestriction(
            payto_regex: self::SAMPLE_PAYTO_REGEX,
            human_hint: self::SAMPLE_HUMAN_HINT,
            human_hint_i18n: self::SAMPLE_I18N
        );

        $this->assertSame(self::SAMPLE_PAYTO_REGEX, $restriction->payto_regex);
        $this->assertSame(self::SAMPLE_HUMAN_HINT, $restriction->human_hint);
        $this->assertSame(self::SAMPLE_I18N, $restriction->human_hint_i18n);
        $this->assertSame('regex', $restriction->getType());
    }

    public function testFromArrayWithRequiredFields(): void
    {
        $data = [
            'payto_regex' => self::SAMPLE_PAYTO_REGEX,
            'human_hint' => self::SAMPLE_HUMAN_HINT
        ];

        $restriction = RegexAccountRestriction::fromArray($data);

        $this->assertSame(self::SAMPLE_PAYTO_REGEX, $restriction->payto_regex);
        $this->assertSame(self::SAMPLE_HUMAN_HINT, $restriction->human_hint);
        $this->assertNull($restriction->human_hint_i18n);
    }

    public function testFromArrayWithAllFields(): void
    {
        $data = [
            'payto_regex' => self::SAMPLE_PAYTO_REGEX,
            'human_hint' => self::SAMPLE_HUMAN_HINT,
            'human_hint_i18n' => self::SAMPLE_I18N
        ];

        $restriction = RegexAccountRestriction::fromArray($data);

        $this->assertSame(self::SAMPLE_PAYTO_REGEX, $restriction->payto_regex);
        $this->assertSame(self::SAMPLE_HUMAN_HINT, $restriction->human_hint);
        $this->assertSame(self::SAMPLE_I18N, $restriction->human_hint_i18n);
    }

    public function testFromArrayWithNullI18n(): void
    {
        $data = [
            'payto_regex' => self::SAMPLE_PAYTO_REGEX,
            'human_hint' => self::SAMPLE_HUMAN_HINT,
            'human_hint_i18n' => null
        ];

        $restriction = RegexAccountRestriction::fromArray($data);

        $this->assertSame(self::SAMPLE_PAYTO_REGEX, $restriction->payto_regex);
        $this->assertSame(self::SAMPLE_HUMAN_HINT, $restriction->human_hint);
        $this->assertNull($restriction->human_hint_i18n);
    }
} 
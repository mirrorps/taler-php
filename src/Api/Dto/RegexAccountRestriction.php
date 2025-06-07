<?php

namespace Taler\Api\Dto;

/**
 * DTO for regex-based account restrictions in the exchange wire account details
 *
 * The regular expression should follow posix-egrep, but without support for character
 * classes, GNU extensions, back-references or intervals.
 *
 * @see https://www.gnu.org/software/findutils/manual/html_node/find_html/posix_002degrep-regular-expression-syntax.html
 * @see https://docs.taler.net/core/api-exchange.html
 */
class RegexAccountRestriction
{
    private const TYPE = 'regex';

    /**
     * @param string $payto_regex Regular expression that the payto://-URI of the partner account must follow
     * @param string $human_hint Hint for a human to understand the restriction
     * @param array<non-empty-string, non-empty-string>|null $human_hint_i18n Map from IETF BCP 47 language tags to localized human hints
     */
    public function __construct(
        public readonly string $payto_regex,
        public readonly string $human_hint,
        public readonly ?array $human_hint_i18n = null,
    ) {
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param array{
     *     payto_regex: string,
     *     human_hint: string,
     *     human_hint_i18n?: array<non-empty-string, non-empty-string>|null
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            payto_regex: $data['payto_regex'],
            human_hint: $data['human_hint'],
            human_hint_i18n: $data['human_hint_i18n'] ?? null
        );
    }

    /**
     * Returns the type of the restriction
     */
    public function getType(): string
    {
        return self::TYPE;
    }
} 
<?php

namespace Taler\Api\Dto;

use Taler\Api\Contract\AccountRestriction;

/**
 * DTO for regex-based account restrictions in the exchange wire account details
 *
 * The regular expression should follow posix-egrep, but without support for character
 * classes, GNU extensions, back-references or intervals.
 *
 * @see https://www.gnu.org/software/findutils/manual/html_node/find_html/posix_002degrep-regular-expression-syntax.html
 * @see https://docs.taler.net/core/api-exchange.html
 */
class RegexAccountRestriction implements AccountRestriction
{
    private const TYPE = 'regex';

    /**
     * @param string $payto_regex Regular expression that the payto://-URI of the partner account must follow
     * @param string $human_hint Hint for a human to understand the restriction
     * @param array<string, string>|null $human_hint_i18n Map from IETF BCP 47 language tags to localized human hints
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
     *     type: string,
     *     payto_regex: string,
     *     human_hint: string,
     *     human_hint_i18n?: array<string, string>|null
     * } $data
     * @throws \InvalidArgumentException if type is missing or not 'regex', or if required fields are missing
     */
    public static function createFromArray(array $data): self
    {
        if (!isset($data['type'])) { // @phpstan-ignore-line - explicitly ensuring the type is properly set
            throw new \InvalidArgumentException('Missing type field');
        }

        if ($data['type'] !== self::TYPE) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid type for RegexAccountRestriction: expected "%s", got "%s"',
                self::TYPE,
                $data['type']
            ));
        }

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
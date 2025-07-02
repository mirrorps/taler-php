<?php

namespace Taler\Api\ContractTerms\Dto;

/**
 * DTO for contract token family data
 * 
 * @see https://docs.taler.net/core/api-common.html
 */
class ContractTokenFamily
{
    /**
     * @param string $name Human-readable name of the token family
     * @param string $description Human-readable description of the semantics of this token family (for display)
     * @param array<string, string>|null $description_i18n Map from IETF BCP 47 language tags to localized descriptions
     * @param array<int, TokenIssueRsaPublicKey|TokenIssueCsPublicKey> $keys Public keys used to validate tokens issued by this token family
     * @param ContractSubscriptionTokenDetails|ContractDiscountTokenDetails $details Kind-specific information of the token
     * @param bool $critical Must a wallet understand this token type to process contracts that use or issue it?
     */
    public function __construct(
        public readonly string $name,
        public readonly string $description,
        public readonly ?array $description_i18n,
        public readonly array $keys,
        public readonly ContractSubscriptionTokenDetails|ContractDiscountTokenDetails $details,
        public readonly bool $critical
    ) {
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param array{
     *     name: string,
     *     description: string,
     *     description_i18n?: array<string, string>|null,
     *     keys: array<int, array{
     *         cipher: 'RSA'|'CS',
     *         rsa_pub?: string,
     *         cs_pub?: string,
     *         signature_validity_start: array{t_s: int|string},
     *         signature_validity_end: array{t_s: int|string}
     *     }>,
     *     details: array{
     *         class: 'subscription'|'discount',
     *         trusted_domains?: array<int, string>,
     *         expected_domains?: array<int, string>
     *     },
     *     critical: bool
     * } $data
     */
    public static function createFromArray(array $data): self
    {
        $keys = array_map(
            function (array $key) {
                $keyData = [
                    'signature_validity_start' => $key['signature_validity_start'],
                    'signature_validity_end' => $key['signature_validity_end']
                ];

                if ($key['cipher'] === 'RSA' && isset($key['rsa_pub'])) {
                    return TokenIssueRsaPublicKey::createFromArray($keyData + ['rsa_pub' => $key['rsa_pub']]);
                }

                if ($key['cipher'] === 'CS' && isset($key['cs_pub'])) {
                    return TokenIssueCsPublicKey::createFromArray($keyData + ['cs_pub' => $key['cs_pub']]);
                }

                throw new \InvalidArgumentException(sprintf(
                    'Invalid or incomplete key data for cipher type "%s"',
                    $key['cipher']
                ));
            },
            $data['keys']
        );

        if ($data['details']['class'] === 'subscription' && isset($data['details']['trusted_domains'])) {
            $details = ContractSubscriptionTokenDetails::createFromArray([
                'trusted_domains' => $data['details']['trusted_domains']
            ]);
        } elseif ($data['details']['class'] === 'discount' && isset($data['details']['expected_domains'])) {
            $details = ContractDiscountTokenDetails::createFromArray([
                'expected_domains' => $data['details']['expected_domains']
            ]);
        } else {
            throw new \InvalidArgumentException(sprintf(
                'Invalid or incomplete token details for class "%s"',
                $data['details']['class']
            ));
        }


        return new self(
            name: $data['name'],
            description: $data['description'],
            description_i18n: $data['description_i18n'] ?? null,
            keys: $keys,
            details: $details,
            critical: $data['critical']
        );
    }
} 
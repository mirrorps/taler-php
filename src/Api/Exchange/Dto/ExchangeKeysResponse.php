<?php

namespace Taler\Api\Exchange\Dto;

use Taler\Api\Contract\DenomGroupCommon;
use Taler\Api\Dto\AggregateTransferFee;
use Taler\Api\Dto\AuditorKeys;
use Taler\Api\Dto\CurrencySpecification;
use Taler\Api\Dto\DenomGroupCs;
use Taler\Api\Dto\DenomGroupCsAgeRestricted;
use Taler\Api\Dto\DenomGroupRsa;
use Taler\Api\Dto\DenomGroupRsaAgeRestricted;
use Taler\Api\Dto\ExtensionManifest;
use Taler\Api\Dto\GlobalFees;
use Taler\Api\Dto\RelativeTime;
use Taler\Api\Dto\SignKey;
use Taler\Api\Dto\ZeroLimitedOperation;
use Taler\Api\Dto\AccountLimit;
use Taler\Api\Dto\Recoup;

/**
 * DTO for exchange keys response from the exchange API
 * 
 * @see https://docs.taler.net/core/api-exchange.html
 */
class ExchangeKeysResponse
{
    /**
     * @param string $version Libtool-style representation of the Exchange protocol version (current:revision:age)
     * @param string $base_url The exchange's base URL
     * @param string $currency The exchange's currency or asset unit
     * @param string|null $shopping_url Shopping URL where users may find shops that accept digital cash from this exchange (since protocol v21)
     * @param string|null $bank_compliance_language Bank-specific language for compliance (since protocol v24)
     * @param CurrencySpecification $currency_specification How wallets should render this currency
     * @param string|null $tiny_amount Small(est?) amount that can likely be transferred to the exchange (since protocol v21)
     * @param string $stefan_abs Absolute cost offset for the STEFAN curve
     * @param string $stefan_log Factor to multiply the logarithm of the amount with to approximate fees
     * @param float $stefan_lin Linear cost factor for the STEFAN curve (scalar multiplied with actual amount)
     * @param string $asset_type Type of the asset: "fiat", "crypto", "regional" or "stock"
     * @param array<int, ExchangeWireAccount> $accounts Array of wire accounts operated by the exchange
     * @param array<string, array<int, AggregateTransferFee>> $wire_fees Object mapping wire method names to wire fees
     * @param array<int, ExchangePartnerListEntry> $wads List of exchanges that this exchange is partnering with
     * @param bool $rewards_allowed Set to true if this exchange allows the use of reserves for rewards (deprecated in protocol v18)
     * @param bool $kyc_enabled Set to true if this exchange has KYC enabled (since protocol v24)
     * @param string $master_public_key EdDSA master public key of the exchange
     * @param RelativeTime $reserve_closing_delay Relative duration until inactive reserves are closed
     * @param array<int, string>|null $wallet_balance_limit_without_kyc Threshold amounts beyond which wallet should trigger KYC
     * @param array<int, AccountLimit> $hard_limits Array of limits that apply to all accounts
     * @param array<int, ZeroLimitedOperation> $zero_limits Array of limits with a soft threshold of zero
     * @param array<int, DenomGroupCommon> $denominations Denominations offered by this exchange
     * @param string $exchange_sig Compact EdDSA signature over the concatenation of all master_sigs
     * @param string $exchange_pub Public EdDSA key of the exchange that was used to generate the signature
     * @param array<int, Recoup> $recoup Denominations for which the exchange currently offers/requests recoup
     * @param array<int, GlobalFees> $global_fees Array of globally applicable fees by time range
     * @param string $list_issue_date The date when the denomination keys were last updated (Timestamp)
     * @param array<int, AuditorKeys> $auditors Auditors of the exchange
     * @param array<int, SignKey> $signkeys The exchange's signing keys
     * @param array<string, ExtensionManifest>|null $extensions Optional field with dictionary of supported extensions
     * @param string|null $extensions_sig Signature by the exchange master key of the extensions field
     */
    public function __construct(
        public readonly string $version,
        public readonly string $base_url,
        public readonly string $currency,
        public readonly ?string $shopping_url,
        public readonly ?string $bank_compliance_language,
        public readonly CurrencySpecification $currency_specification,
        public readonly ?string $tiny_amount,
        public readonly string $stefan_abs,
        public readonly string $stefan_log,
        public readonly float $stefan_lin,
        public readonly string $asset_type,
        public readonly array $accounts,
        public readonly array $wire_fees,
        public readonly array $wads,
        public readonly bool $rewards_allowed,
        public readonly bool $kyc_enabled,
        public readonly string $master_public_key,
        public readonly RelativeTime $reserve_closing_delay,
        public readonly ?array $wallet_balance_limit_without_kyc,
        public readonly array $hard_limits,
        public readonly array $zero_limits,
        public readonly array $denominations,
        public readonly string $exchange_sig,
        public readonly string $exchange_pub,
        public readonly array $recoup,
        public readonly array $global_fees,
        public readonly string $list_issue_date,
        public readonly array $auditors,
        public readonly array $signkeys,
        public readonly ?array $extensions,
        public readonly ?string $extensions_sig,
    ) {
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param array{
     *     version: string,
     *     base_url: string,
     *     currency: string,
     *     shopping_url?: string|null,
     *     bank_compliance_language?: string|null,
     *     currency_specification: array{
     *         name: string,
     *         currency: string,
     *         num_fractional_input_digits: int,
     *         num_fractional_normal_digits: int,
     *         num_fractional_trailing_zero_digits: int,
     *         alt_unit_names: array<numeric-string, string>
     *     },
     *     tiny_amount?: string|null,
     *     stefan_abs: string,
     *     stefan_log: string,
     *     stefan_lin: float,
     *     asset_type: string,
     *     accounts: array<int, array{
     *         payto_uri: string,
     *         master_sig: string,
     *         credit_restrictions?: array<int, array{type: 'deny'}|array{
     *             type: 'regex',
     *             payto_regex: string,
     *             human_hint: string,
     *             human_hint_i18n?: array<string, string>|null
     *         }>,
     *         debit_restrictions?: array<int, array{type: 'deny'}|array{
     *             type: 'regex',
     *             payto_regex: string,
     *             human_hint: string,
     *             human_hint_i18n?: array<string, string>|null
     *         }>,
     *         conversion_url?: string|null,
     *         bank_label?: string|null,
     *         priority?: int|null
     *     }>,
     *     wire_fees: array<string, array<int, array{
     *         wire_fee: string,
     *         closing_fee: string,
     *         wad_fee: string,
     *         start_date: string,
     *         end_date: string,
     *         sig: string
     *     }>>,
     *     wads: array<int, array{
     *         partner_base_url: string,
     *         partner_master_pub: string,
     *         wad_fee: string,
     *         wad_frequency: array{d_us: int|string},
     *         start_date: string,
     *         end_date: string,
     *         master_sig: string
     *     }>,
     *     rewards_allowed: bool,
     *     kyc_enabled: bool,
     *     master_public_key: string,
     *     reserve_closing_delay: array{d_us: int|string},
     *     wallet_balance_limit_without_kyc?: array<int, string>|null,
     *     hard_limits: array<int, array{
     *         operation_type: string,
     *         timeframe: array{d_us: int|string},
     *         threshold: string,
     *         soft_limit?: bool
     *     }>,
     *     zero_limits: array<int, array{
     *         operation_type: string
     *     }>,
     *     denominations: array<int, array{
     *         value: string,
     *         fee_withdraw: string,
     *         fee_deposit: string,
     *         fee_refresh: string,
     *         fee_refund: string,
     *         cipher: string,
     *         denoms: array<int, array{
     *             master_sig: string,
     *             stamp_start: string,
     *             stamp_expire_withdraw: string,
     *             stamp_expire_deposit: string,
     *             stamp_expire_legal: string,
     *             rsa_pub?: string,
     *             cs_pub?: string,
     *             lost?: bool
     *         }>,
     *         age_mask?: string
     *     }>,
     *     exchange_sig: string,
     *     exchange_pub: string,
     *     recoup: array<int, array{h_denom_pub: string}>,
     *     global_fees: array<int, array{
     *         start_date: string,
     *         end_date: string,
     *         history_fee: string,
     *         account_fee: string,
     *         purse_fee: string,
     *         purse_timeout: array{d_us: int|string},
     *         history_expiration: array{d_us: int|string},
     *         purse_account_limit: int,
     *         master_sig: string
     *     }>,
     *     list_issue_date: string,
     *     auditors: array<int, array{
     *         auditor_pub: string,
     *         auditor_url: string,
     *         auditor_name: string,
     *         denomination_keys: array<array{denom_pub_h: string, auditor_sig: string}>
     *     }>,
     *     signkeys: array<int, array{
     *         key: string,
     *         stamp_start: string,
     *         stamp_expire: string,
     *         stamp_end: string,
     *         master_sig: string
     *     }>,
     *     extensions?: array<string, array{
     *         critical: bool,
     *         version: string,
     *         config?: object
     *     }>|null,
     *     extensions_sig?: string|null
     * } $data
     * @return self
     * @throws \InvalidArgumentException When required data is missing or invalid
     */
    public static function fromArray(array $data): self
    {
        // Process wire accounts
        $accounts = [];
        foreach ($data['accounts'] as $accountData) {
            $accounts[] = ExchangeWireAccount::fromArray($accountData);
        }

        // Process wire fees
        $wireFees = [];
        foreach ($data['wire_fees'] as $method => $fees) {
            $wireFees[$method] = array_map(
                fn(array $fee) => AggregateTransferFee::fromArray($fee),
                $fees
            );
        }

        // Process wads (partner exchanges)
        $wads = [];
        foreach ($data['wads'] as $wadData) {
            $wads[] = ExchangePartnerListEntry::fromArray($wadData);
        }

        // Process hard limits
        $hardLimits = [];
        foreach ($data['hard_limits'] as $limitData) {
            $hardLimits[] = AccountLimit::fromArray($limitData);
        }

        // Process zero limits
        $zeroLimits = [];
        foreach ($data['zero_limits'] as $limitData) {
            $zeroLimits[] = ZeroLimitedOperation::fromArray($limitData);
        }

        // Process denominations using factory pattern
        $denominations = [];
        foreach ($data['denominations'] as $denomData) {
            $denominations[] = self::createDenominationGroup($denomData);
        }

        // Process recoup
        $recoup = [];
        foreach ($data['recoup'] as $recoupData) {
            $recoup[] = Recoup::fromArray($recoupData);
        }

        // Process global fees
        $globalFees = [];
        foreach ($data['global_fees'] as $feeData) {
            $globalFees[] = GlobalFees::fromArray($feeData);
        }

        // Process auditors
        $auditors = [];
        foreach ($data['auditors'] as $auditorData) {
            $auditors[] = AuditorKeys::fromArray($auditorData);
        }

        // Process signing keys
        $signkeys = [];
        foreach ($data['signkeys'] as $keyData) {
            $signkeys[] = SignKey::fromArray($keyData);
        }

        // Process extensions if present
        $extensions = null;
        if (isset($data['extensions'])) {
            $extensions = [];
            foreach ($data['extensions'] as $name => $manifestData) {
                $extensions[$name] = ExtensionManifest::fromArray($manifestData);
            }
        }

        return new self(
            version: $data['version'],
            base_url: $data['base_url'],
            currency: $data['currency'],
            shopping_url: $data['shopping_url'] ?? null,
            bank_compliance_language: $data['bank_compliance_language'] ?? null,
            currency_specification: CurrencySpecification::fromArray($data['currency_specification']),
            tiny_amount: $data['tiny_amount'] ?? null,
            stefan_abs: $data['stefan_abs'],
            stefan_log: $data['stefan_log'],
            stefan_lin: $data['stefan_lin'],
            asset_type: $data['asset_type'],
            accounts: $accounts,
            wire_fees: $wireFees,
            wads: $wads,
            rewards_allowed: $data['rewards_allowed'],
            kyc_enabled: $data['kyc_enabled'],
            master_public_key: $data['master_public_key'],
            reserve_closing_delay: RelativeTime::fromArray($data['reserve_closing_delay']),
            wallet_balance_limit_without_kyc: $data['wallet_balance_limit_without_kyc'] ?? null,
            hard_limits: $hardLimits,
            zero_limits: $zeroLimits,
            denominations: $denominations,
            exchange_sig: $data['exchange_sig'],
            exchange_pub: $data['exchange_pub'],
            recoup: $recoup,
            global_fees: $globalFees,
            list_issue_date: $data['list_issue_date'],
            auditors: $auditors,
            signkeys: $signkeys,
            extensions: $extensions,
            extensions_sig: $data['extensions_sig'] ?? null
        );
    }

    /**
     * Factory method to create the appropriate denomination group based on cipher type
     *
     * @param array{
     *     value: string,
     *     fee_withdraw: string,
     *     fee_deposit: string,
     *     fee_refresh: string,
     *     fee_refund: string,
     *     cipher: string,
     *     denoms: array<int, array{
     *         master_sig: string,
     *         stamp_start: string,
     *         stamp_expire_withdraw: string,
     *         stamp_expire_deposit: string,
     *         stamp_expire_legal: string,
     *         rsa_pub?: string,
     *         cs_pub?: string,
     *         lost?: bool
     *     }>,
     *     age_mask?: string
     * } $data
     * @return DenomGroupCommon
     * @throws \InvalidArgumentException When cipher type is not supported
     */
    private static function createDenominationGroup(array $data): DenomGroupCommon
    {
        return match ($data['cipher']) {
            'RSA' => DenomGroupRsa::fromArray($data), // @phpstan-ignore-line - Dynamic factory pattern requires runtime type checking
            'CS' => DenomGroupCs::fromArray($data), // @phpstan-ignore-line - Dynamic factory pattern requires runtime type checking
            'RSA+age_restricted' => DenomGroupRsaAgeRestricted::fromArray($data), // @phpstan-ignore-line - Dynamic factory pattern requires runtime type checking
            'CS+age_restricted' => DenomGroupCsAgeRestricted::fromArray($data), // @phpstan-ignore-line - Dynamic factory pattern requires runtime type checking
            default => throw new \InvalidArgumentException(sprintf(
                'Unsupported denomination cipher type: %s',
                $data['cipher']
            ))
        };
    }
} 
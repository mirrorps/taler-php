<?php

namespace Taler\Tests\Api\Exchange\Dto;

use PHPUnit\Framework\TestCase;
use Taler\Api\Exchange\Dto\ExchangeKeysResponse;
use Taler\Api\Exchange\Dto\ExchangeWireAccount;
use Taler\Api\Exchange\Dto\ExchangePartnerListEntry;
use Taler\Api\Dto\CurrencySpecification;
use Taler\Api\Dto\RelativeTime;
use Taler\Api\Dto\AccountLimit;
use Taler\Api\Dto\ZeroLimitedOperation;
use Taler\Api\Dto\DenomGroupRsa;
use Taler\Api\Dto\DenomGroupCs;
use Taler\Api\Dto\Recoup;
use Taler\Api\Dto\GlobalFees;
use Taler\Api\Dto\AuditorKeys;
use Taler\Api\Dto\SignKey;
use Taler\Api\Dto\ExtensionManifest;
use Taler\Api\Dto\AggregateTransferFee;

class ExchangeKeysResponseTest extends TestCase
{
    private const SAMPLE_VERSION = '12:0:0';
    private const SAMPLE_BASE_URL = 'https://exchange.example.com/';
    private const SAMPLE_CURRENCY = 'EUR';
    private const SAMPLE_SHOPPING_URL = 'https://shop.example.com/';
    private const SAMPLE_BANK_COMPLIANCE_LANGUAGE = 'de';
    private const SAMPLE_TINY_AMOUNT = 'EUR:0.01';
    private const SAMPLE_STEFAN_ABS = 'EUR:0.01';
    private const SAMPLE_STEFAN_LOG = 'EUR:0.01';
    private const SAMPLE_STEFAN_LIN = 0.01;
    private const SAMPLE_ASSET_TYPE = 'fiat';
    private const SAMPLE_REWARDS_ALLOWED = false;
    private const SAMPLE_KYC_ENABLED = true;
    private const SAMPLE_MASTER_PUBLIC_KEY = 'EDDSAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';
    private const SAMPLE_EXCHANGE_SIG = 'EDDSABBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBB';
    private const SAMPLE_EXCHANGE_PUB = 'EDDSACCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCC';
    private const SAMPLE_LIST_ISSUE_DATE = ['t_s' => 1710979200];
    private const SAMPLE_EXTENSIONS_SIG = 'EDDSADDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDD';

    /**
     * @var array{
     *     version: string,
     *     base_url: string,
     *     currency: string,
     *     shopping_url: string,
     *     bank_compliance_language: string,
     *     currency_specification: array{
     *         name: string,
     *         currency: string,
     *         num_fractional_input_digits: int,
     *         num_fractional_normal_digits: int,
     *         num_fractional_trailing_zero_digits: int,
     *         alt_unit_names: array<numeric-string, string>
     *     },
     *     tiny_amount: string,
     *     stefan_abs: string,
     *     stefan_log: string,
     *     stefan_lin: float,
     *     asset_type: string,
     *     accounts: array<int, array{
     *         payto_uri: string,
     *         master_sig: string,
     *         credit_restrictions: array<int, mixed>,
     *         debit_restrictions: array<int, mixed>,
     *         conversion_url: null,
     *         bank_label: string,
     *         priority: int
     *     }>,
     *     wire_fees: array<string, array<int, array{
     *         wire_fee: string,
     *         closing_fee: string,
     *         wad_fee: string,
     *         start_date: array{t_s: int|string},
     *         end_date: array{t_s: int|string},
     *         sig: string
     *     }>>,
     *     wads: array<int, array{
     *         partner_base_url: string,
     *         partner_master_pub: string,
     *         wad_fee: string,
     *         wad_frequency: array{d_us: int|string},
     *         start_date: array{t_s: int|string},
     *         end_date: array{t_s: int|string},
     *         master_sig: string
     *     }>,
     *     rewards_allowed: bool,
     *     kyc_enabled: bool,
     *     master_public_key: string,
     *     reserve_closing_delay: array{d_us: int|string},
     *     wallet_balance_limit_without_kyc: array<int, string>,
     *     hard_limits: array<int, array{
     *         operation_type: string,
     *         timeframe: array{d_us: int|string},
     *         threshold: string,
     *         soft_limit: bool
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
     *             stamp_start: array{t_s: int|string},
     *             stamp_expire_withdraw: array{t_s: int|string},
     *             stamp_expire_deposit: array{t_s: int|string},
     *             stamp_expire_legal: array{t_s: int|string},
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
     *         start_date: array{t_s: int|string},
     *         end_date: array{t_s: int|string},
     *         history_fee: string,
     *         account_fee: string,
     *         purse_fee: string,
     *         purse_timeout: array{d_us: int|string},
     *         history_expiration: array{d_us: int|string},
     *         purse_account_limit: int,
     *         master_sig: string
     *     }>,
     *     list_issue_date: array{t_s: int|string},
     *     auditors: array<int, array{
     *         auditor_pub: string,
     *         auditor_url: string,
     *         auditor_name: string,
     *         denomination_keys: array<int, array{
     *             denom_pub_h: string,
     *             auditor_sig: string
     *         }>
     *     }>,
     *     signkeys: array<int, array{
     *         key: string,
     *         stamp_start: array{t_s: int|string},
     *         stamp_expire: array{t_s: int|string},
     *         stamp_end: array{t_s: int|string},
     *         master_sig: string
     *     }>,
     *     extensions: array<string, array{
     *         critical: bool,
     *         version: string,
     *         config: object
     *     }>,
     *     extensions_sig: string
     * }
     */
    private array $validData;

    protected function setUp(): void
    {
        $this->validData = [
            'version' => self::SAMPLE_VERSION,
            'base_url' => self::SAMPLE_BASE_URL,
            'currency' => self::SAMPLE_CURRENCY,
            'shopping_url' => self::SAMPLE_SHOPPING_URL,
            'bank_compliance_language' => self::SAMPLE_BANK_COMPLIANCE_LANGUAGE,
            'currency_specification' => [
                'name' => 'Euro',
                'currency' => 'EUR',
                'num_fractional_input_digits' => 2,
                'num_fractional_normal_digits' => 2,
                'num_fractional_trailing_zero_digits' => 2,
                'alt_unit_names' => ['0' => 'Euro', '-2' => 'Cent']
            ],
            'tiny_amount' => self::SAMPLE_TINY_AMOUNT,
            'stefan_abs' => self::SAMPLE_STEFAN_ABS,
            'stefan_log' => self::SAMPLE_STEFAN_LOG,
            'stefan_lin' => self::SAMPLE_STEFAN_LIN,
            'asset_type' => self::SAMPLE_ASSET_TYPE,
            'accounts' => [
                [
                    'payto_uri' => 'payto://iban/DE89370400440532013000',
                    'master_sig' => 'EDDSAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA',
                    'credit_restrictions' => [],
                    'debit_restrictions' => [],
                    'conversion_url' => null,
                    'bank_label' => 'Test Bank',
                    'priority' => 0
                ]
            ],
            'wire_fees' => [
                'iban' => [
                    [
                        'wire_fee' => 'EUR:0.01',
                        'closing_fee' => 'EUR:0.01',
                        'wad_fee' => 'EUR:0.01',
                        'start_date' => ['t_s' => 1716153600],
                        'end_date' => ['t_s' => 1716240000],
                        'sig' => 'EDDSAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'
                    ]
                ]
            ],
            'wads' => [
                [
                    'partner_base_url' => 'https://partner.example.com/',
                    'partner_master_pub' => 'EDDSAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA',
                    'wad_fee' => 'EUR:0.01',
                    'wad_frequency' => ['d_us' => 86400000000],
                    'start_date' => ['t_s' => 1716153600],
                    'end_date' => ['t_s' => 1716240000],
                    'master_sig' => 'EDDSAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'
                ]
            ],
            'rewards_allowed' => self::SAMPLE_REWARDS_ALLOWED,
            'kyc_enabled' => self::SAMPLE_KYC_ENABLED,
            'master_public_key' => self::SAMPLE_MASTER_PUBLIC_KEY,
            'reserve_closing_delay' => ['d_us' => 86400000000],
            'wallet_balance_limit_without_kyc' => ['EUR:1000.00'],
            'hard_limits' => [
                [
                    'operation_type' => 'WITHDRAW',
                    'timeframe' => ['d_us' => 86400000000],
                    'threshold' => 'EUR:1000.00',
                    'soft_limit' => false
                ]
            ],
            'zero_limits' => [
                [
                    'operation_type' => 'WITHDRAW'
                ]
            ],
            'denominations' => [
                [
                    'value' => 'EUR:1.00',
                    'fee_withdraw' => 'EUR:0.01',
                    'fee_deposit' => 'EUR:0.01',
                    'fee_refresh' => 'EUR:0.01',
                    'fee_refund' => 'EUR:0.01',
                    'cipher' => 'RSA',
                    'denoms' => [
                        [
                            'master_sig' => 'EDDSAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA',
                            'stamp_start' => ['t_s' => 1710979200],
                            'stamp_expire_withdraw' => ['t_s' => 1711065600],
                            'stamp_expire_deposit' => ['t_s' => 1711152000],
                            'stamp_expire_legal' => ['t_s' => 1711238400],
                            'rsa_pub' => 'RSA-PUB-123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'
                        ]
                    ]
                ]
            ],
            'exchange_sig' => self::SAMPLE_EXCHANGE_SIG,
            'exchange_pub' => self::SAMPLE_EXCHANGE_PUB,
            'recoup' => [
                [
                    'h_denom_pub' => 'SHA512-HASH-AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'
                ]
            ],
            'global_fees' => [
                [
                    'start_date' => ['t_s' => 1710979200],
                    'end_date' => ['t_s' => 1711065600],
                    'history_fee' => 'EUR:0.01',
                    'account_fee' => 'EUR:0.01',
                    'purse_fee' => 'EUR:0.01',
                    'purse_timeout' => ['d_us' => 86400000000],
                    'history_expiration' => ['d_us' => 86400000000],
                    'purse_account_limit' => 5,
                    'master_sig' => 'EDDSAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'
                ]
            ],
            'list_issue_date' => self::SAMPLE_LIST_ISSUE_DATE,
            'auditors' => [
                [
                    'auditor_pub' => 'EDDSAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA',
                    'auditor_url' => 'https://auditor.example.com/',
                    'auditor_name' => 'Test Auditor',
                    'denomination_keys' => [
                        [
                            'denom_pub_h' => 'SHA512-HASH-AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA',
                            'auditor_sig' => 'EDDSAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'
                        ]
                    ]
                ]
            ],
            'signkeys' => [
                [
                    'key' => 'EDDSAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA',
                    'stamp_start' => ['t_s' => 1710979200],
                    'stamp_expire' => ['t_s' => 1711065600],
                    'stamp_end' => ['t_s' => 1711152000],
                    'master_sig' => 'EDDSAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'
                ]
            ],
            'extensions' => [
                'age_restriction' => [
                    'critical' => false,
                    'version' => '1:0:0',
                    'config' => (object)['age_groups' => [0, 13, 16, 18, 21]]
                ]
            ],
            'extensions_sig' => self::SAMPLE_EXTENSIONS_SIG
        ];
    }

    public function testFromArrayWithValidData(): void
    {
        $response = ExchangeKeysResponse::fromArray($this->validData);

        $this->assertSame(self::SAMPLE_VERSION, $response->version);
        $this->assertSame(self::SAMPLE_BASE_URL, $response->base_url);
        $this->assertSame(self::SAMPLE_CURRENCY, $response->currency);
        $this->assertSame(self::SAMPLE_SHOPPING_URL, $response->shopping_url);
        $this->assertSame(self::SAMPLE_BANK_COMPLIANCE_LANGUAGE, $response->bank_compliance_language);
        $this->assertInstanceOf(CurrencySpecification::class, $response->currency_specification);
        $this->assertSame(self::SAMPLE_TINY_AMOUNT, $response->tiny_amount);
        $this->assertSame(self::SAMPLE_STEFAN_ABS, $response->stefan_abs);
        $this->assertSame(self::SAMPLE_STEFAN_LOG, $response->stefan_log);
        $this->assertSame(self::SAMPLE_STEFAN_LIN, $response->stefan_lin);
        $this->assertSame(self::SAMPLE_ASSET_TYPE, $response->asset_type);
        $this->assertCount(1, $response->accounts);
        $this->assertInstanceOf(ExchangeWireAccount::class, $response->accounts[0]);
        $this->assertArrayHasKey('iban', $response->wire_fees);
        $this->assertCount(1, $response->wads);
        $this->assertInstanceOf(ExchangePartnerListEntry::class, $response->wads[0]);
        $this->assertSame(self::SAMPLE_REWARDS_ALLOWED, $response->rewards_allowed);
        $this->assertSame(self::SAMPLE_KYC_ENABLED, $response->kyc_enabled);
        $this->assertSame(self::SAMPLE_MASTER_PUBLIC_KEY, $response->master_public_key);
        $this->assertInstanceOf(RelativeTime::class, $response->reserve_closing_delay);
        $this->assertSame(['EUR:1000.00'], $response->wallet_balance_limit_without_kyc);
        $this->assertCount(1, $response->hard_limits);
        $this->assertInstanceOf(AccountLimit::class, $response->hard_limits[0]);
        $this->assertCount(1, $response->zero_limits);
        $this->assertInstanceOf(ZeroLimitedOperation::class, $response->zero_limits[0]);
        $this->assertCount(1, $response->denominations);
        $this->assertInstanceOf(DenomGroupRsa::class, $response->denominations[0]);
        $this->assertSame(self::SAMPLE_EXCHANGE_SIG, $response->exchange_sig);
        $this->assertSame(self::SAMPLE_EXCHANGE_PUB, $response->exchange_pub);
        $this->assertCount(1, $response->recoup);
        $this->assertInstanceOf(Recoup::class, $response->recoup[0]);
        $this->assertCount(1, $response->global_fees);
        $this->assertInstanceOf(GlobalFees::class, $response->global_fees[0]);
        $this->assertSame(self::SAMPLE_LIST_ISSUE_DATE['t_s'], $response->list_issue_date->t_s);
        $this->assertCount(1, $response->auditors);
        $this->assertInstanceOf(AuditorKeys::class, $response->auditors[0]);
        $this->assertCount(1, $response->signkeys);
        $this->assertInstanceOf(SignKey::class, $response->signkeys[0]);
        $this->assertArrayHasKey('age_restriction', $response->extensions);
        $this->assertInstanceOf(ExtensionManifest::class, $response->extensions['age_restriction']);
        $this->assertSame(self::SAMPLE_EXTENSIONS_SIG, $response->extensions_sig);
    }

    public function testFromArrayWithOptionalFieldsNull(): void
    {
        $data = $this->validData;
        unset($data['shopping_url']);
        unset($data['bank_compliance_language']);
        unset($data['tiny_amount']);
        unset($data['wallet_balance_limit_without_kyc']);
        unset($data['extensions']);
        unset($data['extensions_sig']);

        $response = ExchangeKeysResponse::fromArray($data);

        $this->assertNull($response->shopping_url);
        $this->assertNull($response->bank_compliance_language);
        $this->assertNull($response->tiny_amount);
        $this->assertNull($response->wallet_balance_limit_without_kyc);
        $this->assertNull($response->extensions);
        $this->assertNull($response->extensions_sig);
    }

    public function testFromArrayWithCsDenominations(): void
    {
        $data = $this->validData;
        $data['denominations'][0]['cipher'] = 'CS';
        $data['denominations'][0]['denoms'][0]['cs_pub'] = 'CS25519-PUB-123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        unset($data['denominations'][0]['denoms'][0]['rsa_pub']);

        $response = ExchangeKeysResponse::fromArray($data);

        $this->assertInstanceOf(DenomGroupCs::class, $response->denominations[0]);
    }

    public function testFromArrayWithAgeRestrictedDenominations(): void
    {
        $data = $this->validData;
        $data['denominations'][0]['cipher'] = 'RSA+age_restricted';
        $data['denominations'][0]['age_mask'] = '0000000000000000000000000000000000000000000000000000000000000001';

        $response = ExchangeKeysResponse::fromArray($data);

        $this->assertInstanceOf(\Taler\Api\Dto\DenomGroupRsaAgeRestricted::class, $response->denominations[0]);
    }

    public function testFromArrayWithUnsupportedDenominationCipher(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported denomination cipher type: UNKNOWN');

        $data = $this->validData;
        $data['denominations'][0]['cipher'] = 'UNKNOWN';

        ExchangeKeysResponse::fromArray($data);
    }

    public function testFromArrayWithEmptyArrays(): void
    {
        $data = $this->validData;
        $data['accounts'] = [];
        $data['wire_fees'] = [];
        $data['wads'] = [];
        $data['hard_limits'] = [];
        $data['zero_limits'] = [];
        $data['denominations'] = [];
        $data['recoup'] = [];
        $data['global_fees'] = [];
        $data['auditors'] = [];
        $data['signkeys'] = [];

        $response = ExchangeKeysResponse::fromArray($data);

        $this->assertEmpty($response->accounts);
        $this->assertEmpty($response->wire_fees);
        $this->assertEmpty($response->wads);
        $this->assertEmpty($response->hard_limits);
        $this->assertEmpty($response->zero_limits);
        $this->assertEmpty($response->denominations);
        $this->assertEmpty($response->recoup);
        $this->assertEmpty($response->global_fees);
        $this->assertEmpty($response->auditors);
        $this->assertEmpty($response->signkeys);
    }

    public function testObjectImmutability(): void
    {
        $response = ExchangeKeysResponse::fromArray($this->validData);

        $this->assertTrue((new \ReflectionProperty($response, 'version'))->isReadOnly());
        $this->assertTrue((new \ReflectionProperty($response, 'base_url'))->isReadOnly());
        $this->assertTrue((new \ReflectionProperty($response, 'currency'))->isReadOnly());
        $this->assertTrue((new \ReflectionProperty($response, 'master_public_key'))->isReadOnly());
        $this->assertTrue((new \ReflectionProperty($response, 'exchange_sig'))->isReadOnly());
        $this->assertTrue((new \ReflectionProperty($response, 'exchange_pub'))->isReadOnly());
        $this->assertTrue((new \ReflectionProperty($response, 'list_issue_date'))->isReadOnly());
    }
} 
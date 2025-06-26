<?php

namespace Taler\Api\Exchange\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\Exchange\ExchangeClient;
use Taler\Api\Exchange\Dto\ExchangeKeysResponse;
use Taler\Exception\TalerException;

class Keys
{
    public function __construct(
        private ExchangeClient $exchangeClient
    ) {}

    /**
     * @param array<string, string> $headers HTTP headers
     * @return ExchangeKeysResponse|array{
     *     master_public_key: string,
     *     exchange_pub: string,
     *     exchange_sig: string,
     *     list_issue_date: array{t_s: int},
     *     denominations: array<int, array{
     *         master_sig: string,
     *         cipher: string,
     *         value: string,
     *         stamp_start: array{t_s: int},
     *         stamp_expire_withdraw: array{t_s: int},
     *         stamp_expire_deposit: array{t_s: int},
     *         stamp_expire_legal: array{t_s: int},
     *         denom_pub: string,
     *         fee_withdraw: array{
     *             fees: array<int, array{
     *                 start_date: array{t_s: int},
     *                 end_date: array{t_s: int},
     *                 fee: string
     *             }>
     *         },
     *         fee_deposit: array{
     *             fees: array<int, array{
     *                 start_date: array{t_s: int},
     *                 end_date: array{t_s: int},
     *                 fee: string
     *             }>
     *         },
     *         fee_refresh: array{
     *             fees: array<int, array{
     *                 start_date: array{t_s: int},
     *                 end_date: array{t_s: int},
     *                 fee: string
     *             }>
     *         },
     *         fee_refund: array{
     *             fees: array<int, array{
     *                 start_date: array{t_s: int},
     *                 end_date: array{t_s: int},
     *                 fee: string
     *             }>
     *         }
     *     }>
     * }
     */
    public static function run(
        ExchangeClient $exchangeClient,
        array $params = [],
        array $headers = []
    ): ExchangeKeysResponse|array {
        
        $keys = new self($exchangeClient);

        try {
            $cacheWrapper = $exchangeClient->getTaler()->getCacheWrapper();
            $cacheKey = $cacheWrapper?->getCacheKey() ?? 'exchange_keys';
            
            // If caching is enabled, try to get from cache
            if ($cacheWrapper?->getTtl() !== null) {
                $cachedResult = $cacheWrapper->getCache()->get($cacheKey);
                if ($cachedResult !== null) {
                    $cacheWrapper->clearCacheSettings();
                    return $cachedResult;
                }
            }

            $keys->exchangeClient->setResponse(
                $keys->exchangeClient->getClient()->request('GET', 'keys?' . http_build_query($params), $headers)
            );

            /** @var ExchangeKeysResponse|array{
             *     master_public_key: string,
             *     exchange_pub: string,
             *     exchange_sig: string,
             *     list_issue_date: array{t_s: int},
             *     denominations: array<int, array{
             *         master_sig: string,
             *         cipher: string,
             *         value: string,
             *         stamp_start: array{t_s: int},
             *         stamp_expire_withdraw: array{t_s: int},
             *         stamp_expire_deposit: array{t_s: int},
             *         stamp_expire_legal: array{t_s: int},
             *         denom_pub: string,
             *         fee_withdraw: array{fees: array<int, array{start_date: array{t_s: int}, end_date: array{t_s: int}, fee: string}>},
             *         fee_deposit: array{fees: array<int, array{start_date: array{t_s: int}, end_date: array{t_s: int}, fee: string}>},
             *         fee_refresh: array{fees: array<int, array{start_date: array{t_s: int}, end_date: array{t_s: int}, fee: string}>},
             *         fee_refund: array{fees: array<int, array{start_date: array{t_s: int}, end_date: array{t_s: int}, fee: string}>}
             *     }>
             * } $result */
            $result = $keys->exchangeClient->handleWrappedResponse($keys->handleResponse(...));

            // If caching was enabled, store in cache
            if ($cacheWrapper?->getTtl() !== null) {
                $cacheWrapper->getCache()->set(
                    $cacheKey,
                    $result,
                    $cacheWrapper->getTtl()
                );
            }
            
            // Clear cache settings for next call
            $cacheWrapper?->clearCacheSettings();
            
            return $result;
        } catch (\Throwable $e) {
            $cacheWrapper?->clearCacheSettings();
            throw $e;
        }
    }

    /**
     * Handle the keys response and return the appropriate DTO
     */
    private function handleResponse(ResponseInterface $response): ExchangeKeysResponse
    {
        /** @var array{
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
         *         denomination_keys: array<array{denom_pub_h: string, auditor_sig: string}>
         *     }>,
         *     signkeys: array<int, array{
         *         key: string,
         *         stamp_start: array{t_s: int|string},
         *         stamp_expire: array{t_s: int|string},
         *         stamp_end: array{t_s: int|string},
         *         master_sig: string
         *     }>,
         *     extensions?: array<string, array{
         *         critical: bool,
         *         version: string,
         *         config?: object
         *     }>|null,
         *     extensions_sig?: string|null
         * } $data */

        $data = $this->exchangeClient->parseResponseBody($response, 200);

         return ExchangeKeysResponse::fromArray($data); 
    }

    /**
     * @param array<string, string> $headers HTTP headers
     * @return mixed
     */
    public static function runAsync(
        ExchangeClient $exchangeClient,
        array $params = [],
        array $headers = []
    ): mixed {

        $keys = new self($exchangeClient);

        return $keys->exchangeClient
            ->getClient()
            ->requestAsync('GET', 'keys', $headers)
            ->then(function (ResponseInterface $response) use ($keys) {
                $keys->exchangeClient->setResponse($response);
                return $keys->exchangeClient->handleWrappedResponse($keys->handleResponse(...));
            });
    }
} 
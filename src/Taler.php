<?php

namespace Taler;

use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Psr\SimpleCache\CacheInterface;
use Taler\Api\BankAccounts\BankAccountClient;
use Taler\Api\OtpDevices\OtpDevicesClient;
use Taler\Api\Inventory\InventoryClient;
use Taler\Api\Instance\InstanceClient;
use Taler\Api\WireTransfers\WireTransfersClient;
use Taler\Config\TalerConfig;
use Taler\Http\HttpClientWrapper;
use Taler\Cache\CacheWrapper;
use Taler\Api\Order\OrderClient;
use Taler\Api\Templates\TemplatesClient;
use Taler\Api\Wallet\WalletClient;
use Taler\Api\Webhooks\WebhooksClient;
use Taler\Api\TokenFamilies\TokenFamiliesClient;
use \Taler\Api\Config\ConfigClient;
use Taler\Api\DonauCharity\DonauCharityClient;
use Taler\Api\TwoFactorAuth\TwoFactorAuthClient;

class Taler
{
    public const TALER_PROTOCOL_VERSION = '20';

    protected HttpClientWrapper $httpClientWrapper;
    protected ?CacheWrapper $cacheWrapper;
    protected OrderClient $order;
    protected WalletClient $wallet;
    protected BankAccountClient $bankAccount;
    protected WireTransfersClient $wireTransfers;
    protected OtpDevicesClient $otpDevices;
    protected TemplatesClient $templates;
    protected TokenFamiliesClient $tokenFamilies;
    protected WebhooksClient $webhooks;
    protected InventoryClient $inventory;
    protected InstanceClient $instance;
    protected ConfigClient $configApi;
    protected DonauCharityClient $donauCharity;
    protected TwoFactorAuthClient $twoFactorAuth;

    /**
     * Taler constructor.
     * 
     * Creates a new instance of the Taler client with the given configuration and HTTP client.
     * 
     * @param TalerConfig $config The configuration for the Taler client
     * @param ClientInterface|null $client Optional PSR-18 HTTP client implementation
     * @param LoggerInterface|null $logger Optional PSR-3 logger implementation
     * @param CacheInterface|null $cache Optional PSR-16 cache implementation
     */
    public function __construct(
        protected TalerConfig $config,
        protected ?ClientInterface $client = null,
        protected ?LoggerInterface $logger = null,
        protected ?CacheInterface $cache = null
    )
    {
        $this->httpClientWrapper = new HttpClientWrapper($config, $client, $logger);
        $this->cacheWrapper = $cache ? new CacheWrapper($cache) : null;
        $this->logger ??= new NullLogger();
    }

    /**
     * Get the HTTP client wrapper instance
     * 
     * @return HttpClientWrapper The configured HTTP client wrapper
     */
    public function getHttpClientWrapper(): HttpClientWrapper
    {
        return $this->httpClientWrapper;
    }

    public function getCacheWrapper(): ?CacheWrapper
    {
        return $this->cacheWrapper;
    }

    /**
     * Get the Taler configuration instance
     * 
     * @return TalerConfig The current configuration
     */
    public function getConfig(): TalerConfig
    {
        return $this->config;
    }

    /**
     * Update configuration with new values
     * 
     * Allows fluent configuration updates by returning $this.
     * 
     * @param array<string, mixed> $config Array of configuration values to update
     * @return self Returns $this for method chaining
     * @throws \InvalidArgumentException When any of the configuration attributes do not exist
     */
    public function config(array $config): self
    {
        $this->getConfig()->setAttributes($config);
        return $this;
    }


    /**
     * Get the logger instance
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Get the Order API client instance
     * 
     * Creates a new instance if one doesn't exist yet, otherwise returns the existing instance.
     * 
     * @return OrderClient The Order API client
     */
    public function order(): OrderClient
    {
        $this->order ??= new OrderClient(
            $this,
            $this->httpClientWrapper
        );

        return $this->order;
    }

    /**
     * Get the Wallet API client instance
     * 
     * Creates a new instance if one doesn't exist yet, otherwise returns the existing instance.
     * 
     * @return WalletClient The Wallet API client
     */
    public function wallet(): WalletClient
    {
        $this->wallet ??= new WalletClient(
            $this,
            $this->httpClientWrapper
        );

        return $this->wallet;
    }

    /**
     * Get the Bank Account API client instance
     * 
     * Creates a new instance if one doesn't exist yet, otherwise returns the existing instance.
     * 
     * @return BankAccountClient The Bank Account API client
     */
    public function bankAccount(): BankAccountClient
    {
        $this->bankAccount ??= new BankAccountClient(
            $this,
            $this->httpClientWrapper
        );

        return $this->bankAccount;
    }

    /**
     * Get the Wire Transfers API client instance
     *
     * @return WireTransfersClient
     */
    public function wireTransfers(): WireTransfersClient
    {
        $this->wireTransfers ??= new WireTransfersClient(
            $this,
            $this->httpClientWrapper
        );

        return $this->wireTransfers;
    }

    /**
     * Get the OTP Devices API client instance
     *
     * @return OtpDevicesClient
     */
    public function otpDevices(): OtpDevicesClient
    {
        $this->otpDevices ??= new OtpDevicesClient(
            $this,
            $this->httpClientWrapper
        );

        return $this->otpDevices;
    }

    /**
     * Get the Templates API client instance
     *
     * @return TemplatesClient
     */
    public function templates(): TemplatesClient
    {
        $this->templates ??= new TemplatesClient(
            $this,
            $this->httpClientWrapper
        );

        return $this->templates;
    }

    /**
     * Get the Webhooks API client instance
     *
     * @return WebhooksClient
     */
    public function webhooks(): WebhooksClient
    {
        $this->webhooks ??= new WebhooksClient(
            $this,
            $this->httpClientWrapper
        );

        return $this->webhooks;
    }

    /**
     * Get the Token Families API client instance
     *
     * @return \Taler\Api\TokenFamilies\TokenFamiliesClient
     */
    public function tokenFamilies(): \Taler\Api\TokenFamilies\TokenFamiliesClient
    {
        $this->tokenFamilies ??= new \Taler\Api\TokenFamilies\TokenFamiliesClient(
            $this,
            $this->httpClientWrapper
        );

        return $this->tokenFamilies;
    }

    /**
     * Get the Inventory API client instance
     *
     * @return InventoryClient
     */
    public function inventory(): InventoryClient
    {
        $this->inventory ??= new InventoryClient(
            $this,
            $this->httpClientWrapper
        );

        return $this->inventory;
    }

    /**
     * Get the Instance API client instance
     *
     * @return InstanceClient
     */
    public function instance(): InstanceClient
    {
        $this->instance ??= new InstanceClient(
            $this,
            $this->httpClientWrapper
        );

        return $this->instance;
    }

    /**
     * Get the Config API client instance
     *
     * @return ConfigClient
     */
    public function configApi(): ConfigClient
    {
        $this->configApi ??= new ConfigClient(
            $this,
            $this->httpClientWrapper
        );

        return $this->configApi;
    }

    /**
     * Get the Donau Charity API client instance
     *
     * @return DonauCharityClient
     */
    public function donauCharity(): DonauCharityClient
    {
        $this->donauCharity ??= new DonauCharityClient(
            $this,
            $this->httpClientWrapper
        );

        return $this->donauCharity;
    }

    /**
     * Get the Two Factor Auth API client instance
     *
     * @return TwoFactorAuthClient
     */
    public function twoFactorAuth(): TwoFactorAuthClient
    {
        $this->twoFactorAuth ??= new TwoFactorAuthClient(
            $this, 
            $this->httpClientWrapper
        );
        
        return $this->twoFactorAuth;
    }

    /**
     * Enable caching for the next API call with specified TTL in minutes
     *
     * @param int $minutes Time to live in minutes
     * @return static
     */
    public function cache(int $minutes, ?string $cacheKey = null): static
    {
        $cacheWrapper = $this->getCacheWrapper();

        if($cacheWrapper === null) {
            throw new \Exception('Cache is not set');
        }

        $cacheWrapper->setTtl($minutes * 60); // Convert to seconds
        
        if($cacheKey !== null) {
            $cacheWrapper->setCacheKey($cacheKey);
        }

        return $this;
    }

    public function cacheDelete(string $cacheKey): static
    {
        $cacheWrapper = $this->getCacheWrapper();

        if($cacheWrapper === null) {
            throw new \Exception('Cache is not set');
        }

        $cacheWrapper->getCache()->delete($cacheKey);

        return $this;
    }

    /**
     * Diagnose current configuration and credentials using this instance.
     *
     * Steps:
     *  - GET /config (always)
     *  - If auth token is non-empty: GET private (instance info)
     *  - If auth token is non-empty: GET private/orders?limit=1 (auth-required)
     *
     * Returns a structured report with per-step status and overall ok flag.
     * Exceptions encountered are returned under the 'exception' key.
     *
     * @return array{
     *   ok: bool,
     *   config: array{ok: bool, status: int|null, error: string|null, exception?: \Throwable|null},
     *   instance?: array{ok: bool, status: int|null, error: string|null, exception?: \Throwable|null},
     *   auth?: array{ok: bool, status: int|null, error: string|null, exception?: \Throwable|null}
     * }
     */
    public function configCheck(): array
    {
        $report = [
            'ok' => false,
            'config' => ['ok' => false, 'status' => null, 'error' => null],
        ];

        // 1) /config must be reachable
        try {
            $this->configApi()->getConfig();
            $report['config'] = ['ok' => true, 'status' => 200, 'error' => null];
        } catch (\Taler\Exception\TalerException $e) {
            $status = $e->getCode() ?: null;
            $msg = ($status === 404) ? 'config_not_found' : 'config_request_failed';
            $report['config'] = [
                'ok' => false,
                'status' => is_int($status) ? $status : null,
                'error' => $msg,
                'exception' => $e,
            ];
        } catch (\Throwable $e) {
            $report['config'] = [
                'ok' => false,
                'status' => null,
                'error' => 'config_request_failed',
                'exception' => $e,
            ];
        }

        $token = $this->getConfig()->getAuthToken();
        $instanceChecked = false;
        $instanceOk = false;
        $authChecked = false;
        $authOk = false;

        // Only proceed with instance/auth checks if token is provided (empty accepted -> skip)
        if ($token !== '') {
            $instanceChecked = true;

            // 2) Instance information via current base: GET 'private'
            try {
                $response = $this->getHttpClientWrapper()->request('GET', 'private');
                $status = $response->getStatusCode();
                if ($status !== 200) {
                    throw new \Taler\Exception\TalerException('Unexpected response status code: ' . $status, $status, null, $response);
                }
                $instanceOk = true;
                $report['instance'] = ['ok' => true, 'status' => 200, 'error' => null];
            } catch (\Taler\Exception\TalerException $e) {
                $status = $e->getCode() ?: null;
                $msg = match ($status) {
                    404 => 'instance_not_found',
                    401 => 'unauthorized',
                    default => 'instance_request_failed',
                };
                $report['instance'] = [
                    'ok' => false,
                    'status' => is_int($status) ? $status : null,
                    'error' => $msg,
                    'exception' => $e,
                ];
            } catch (\Throwable $e) {
                $report['instance'] = [
                    'ok' => false,
                    'status' => null,
                    'error' => 'instance_request_failed',
                    'exception' => $e,
                ];
            }

            // 3) Auth-required harmless call: GET 'private/orders?limit=1'
            $authChecked = true;
            try {
                $this->order()->getOrders(['limit' => '1']);
                $authOk = true;
                $report['auth'] = ['ok' => true, 'status' => 200, 'error' => null];
            } catch (\Taler\Exception\TalerException $e) {
                $status = $e->getCode() ?: null;
                $msg = match ($status) {
                    401 => 'unauthorized',
                    404 => 'instance_not_found',
                    default => 'auth_request_failed',
                };
                $report['auth'] = [
                    'ok' => false,
                    'status' => is_int($status) ? $status : null,
                    'error' => $msg,
                    'exception' => $e,
                ];
            } catch (\Throwable $e) {
                $report['auth'] = [
                    'ok' => false,
                    'status' => null,
                    'error' => 'auth_request_failed',
                    'exception' => $e,
                ];
            }
        }

        $overall = $report['config']['ok']
            && (!$instanceChecked || $instanceOk)
            && (!$authChecked || $authOk);
        $report['ok'] = $overall;

        return $report;
    }

    
}

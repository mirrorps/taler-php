# TalerPHP

TalerPHP is a PHP SDK for interacting with GNU Taler payment systems. It provides a simple, secure way to integrate Taler payments into your PHP applications and services.

**⚠️ Early Development Notice**
>
> TalerPHP is currently in early development. The API and features are subject to change, and the package is **not yet ready for production use**. We welcome feedback and contributions, but please use with caution and do not rely on this package for critical or production systems at this stage
---

## Features

- Easy API for interacting with Taler services
- PSR-4 autoloading
- Extensible and testable architecture

---

## Prerequisites

Before you begin, ensure you have met the following requirements:

- PHP 8.1 or newer
- Composer
- (Optional) PHPUnit for running tests

---

## Installation

Install TalerPHP via Composer:

```
composer require mirrorps/taler-php
```

Requirements
```
• A PSR-18 HTTP client implementation (e.g., Guzzle, Symfony HttpClient)
• A PSR-17 HTTP factory implementation (e.g., Nyholm/psr7, guzzlehttp/psr7 version 2 or higher)
• Optional: For async request support, you need a client that implements the `HttpAsyncClient` interface from `php-http/httplug`
```
---

## Usage
```
require "vendor/autoload.php";
use TalerPHP\Factory;

$taler = Factory::create([
    'base_url' => 'https://backend.demo.taler.net/instances/sandbox',
    'token'    => 'Bearer token'
]);

```

---

## Configuration

- `base_url`: The URL of your Taler backend instance.
- `token`: Your authentication token ( ⚠️ do **not** hardcode; use environment variables or secure storage in your application).
- `wrapResponse`: (Optional) Boolean flag to control DTO wrapping of responses. Defaults to `true`. When set to `false`, methods return raw array responses from Taler.
- `httpClient`: (Optional) A PSR-18 compatible HTTP client instance.

### Basic Example
```php
$taler = Factory::create([
    'base_url' => 'https://backend.demo.taler.net/instances/sandbox',
    'token'    => 'Bearer token',
    'wrapResponse' => true // Optional, defaults to true
]);
```

### Using Custom HTTP Client

If the SDK's auto-discovery doesn't find a PSR-18 compatible HTTP client, or if you want to use a specific client implementation, you can provide your own. Here's an example using Guzzle:

```php
use Http\Adapter\Guzzle7\Client as GuzzleAdapter;

// Create PSR-18 client using Guzzle
$httpClient = GuzzleAdapter::createWithConfig(['timeout' => 30]);

$taler = Factory::create([
    'base_url' => 'https://backend.demo.taler.net/instances/sandbox',
    'token'    => 'Bearer token',
    'client' => $httpClient
]);
```

---

## Exchange API

The Exchange API provides functionality to interact with Taler exchange services. Here's how to use it:

### Basic Setup

```php
use TalerPHP\Factory;

$taler = Factory::create([
    'base_url' => 'https://exchange.demo.taler.net',
    'token'    => 'Bearer token'
]);

$exchange = $taler->exchange();
```

### Available Methods

#### Get Exchange Configuration

Retrieve the exchange's configuration and version information:

```php
$config = $exchange->getConfig();

// Access configuration details
echo $config->version;        // Exchange protocol version
echo $config->name;          // Protocol name (should be "taler-exchange")
echo $config->currency;      // Supported currency (e.g., "EUR")
```

#### Get Exchange Keys

Fetch the exchange's cryptographic keys and related information:

```php
$keys = $exchange->getKeys();

// Access keys and related data
echo $keys->base_url;           // Exchange's base URL
echo $keys->currency;           // Exchange's currency
echo $keys->master_public_key;  // EdDSA master public key
```

#### Track Wire Transfers

Track a specific wire transfer by its ID:

```php
$wtid = "your-wire-transfer-id";
$transfer = $exchange->getTransfer($wtid);

// Access transfer details
echo $transfer->total;          // Transfer amount excluding wire fee
echo $transfer->wire_fee;       // Applied wire fee
echo $transfer->merchant_pub;   // Merchant's public key
```

#### Track Deposits

Track deposits for a specific transaction:

```php
$deposits = $exchange->getDeposits(
    H_WIRE: "wire-hash",
    MERCHANT_PUB: "merchant-public-key",
    H_CONTRACT_TERMS: "contract-terms-hash",
    COIN_PUB: "coin-public-key",
    merchant_sig: "merchant-signature"
);

// Access deposit details
if ($deposits instanceof TrackTransactionResponse) {
    echo $deposits->wtid;              // Wire transfer ID
    echo $deposits->coin_contribution; // Coin's contribution to total
} elseif ($deposits instanceof TrackTransactionAcceptedResponse) {
    echo $deposits->kyc_ok;           // KYC check status
    echo $deposits->execution_time;    // Expected execution time
}
```

### Asynchronous Operations

All methods support asynchronous operations with the `Async` suffix:

```php
$configPromise = $exchange->getConfigAsync();
$keysPromise = $exchange->getKeysAsync();

// Handle promises according to your async client implementation
$configPromise->then(function ($config) {
    // Handle configuration response
});
```

### Error Handling

The Exchange API methods may throw `TalerException` for various error conditions:

```php
use Taler\Exception\TalerException;

try {
    $config = $exchange->getConfig();
} catch (TalerException $e) {
    // Handle Taler-specific errors
    echo $e->getMessage();
} catch (\Throwable $e) {
    // Handle other errors
    echo $e->getMessage();
}
```

### Response Configuration

By default, all responses are wrapped in corresponding DTO objects. However, you can configure this behavior at runtime:

```php
// Disable DTO wrapping to get raw array responses
// Now methods will return the original array response from Taler
$config = $taler->config(['wrapResponse' => false])->exchange()->getConfig();

/*
Array response example:
[
    'version' => 'current:revision:age',
    'name' => 'taler-exchange',
    'currency' => 'EUR',
    'currency_specification' => [
        'name' => 'Euro',
        'currency' => 'EUR',
        'num_fractional_input_digits' => 2,
        'num_fractional_normal_digits' => 2,
        'num_fractional_trailing_zero_digits' => 2,
        'alt_unit_names' => []
    ],
    'supported_kyc_requirements' => [],
    // ... other fields
]
*/

```


---
## Logging

The TalerPHP SDK supports logging through PSR-3 LoggerInterface. You can provide your own PSR-3 compatible logger (like Monolog) for logging of API interactions.

### Basic Logging Setup

```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Create a Monolog logger
$logger = new Logger('taler');
$logger->pushHandler(new StreamHandler('path/to/your.log', Logger::DEBUG));

// Initialize Taler with logger
$taler = Factory::create([
    'base_url' => 'https://backend.demo.taler.net/instances/sandbox',
    'token'    => 'Bearer token',
    'logger'   => $logger
]);
```

### What Gets Logged

The SDK logs the following information:

- HTTP request details (URL, method, headers, body)
- HTTP response details (status code, headers, body)
- Error conditions and exceptions
- API operation failures

### Log Levels Used

- **DEBUG**: Detailed request/response information
- **ERROR**: API errors, request failures, and exceptions

### Example Log Output

```
[2024-03-21 10:15:30] taler.DEBUG: Taler request: https://backend.demo.taler.net/instances/sandbox/config, GET
[2024-03-21 10:15:30] taler.DEBUG: Taler request headers: {"User-Agent":["Mirrorps_Taler_PHP"],"Authorization":["Bearer token"]}
[2024-03-21 10:15:31] taler.DEBUG: Taler response: 200, OK
[2024-03-21 10:15:31] taler.DEBUG: Taler response headers: {"Content-Type":["application/json"]}
[2024-03-21 10:15:31] taler.DEBUG: Taler response body: {"version":"0.8.0","name":"taler-exchange"}
```

### Custom Logger

If you want to implement your own logger, it must implement `Psr\Log\LoggerInterface`.

---

## Caching

The TalerPHP SDK supports caching through PSR-16 SimpleCache interface. You can provide any PSR-16 compatible cache implementation to store API responses and reduce unnecessary network requests.

### Basic Cache Setup

```php
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;

// Create a PSR-16 cache implementation
$filesystemAdapter = new FilesystemAdapter();
$cache = new Psr16Cache($filesystemAdapter);

// Initialize Taler with cache
$taler = Factory::create([
    'base_url' => 'https://backend.demo.taler.net/instances/sandbox',
    'token'    => 'Bearer token',
    'cache'    => $cache
]);
```

### Using Cache with API Calls

You can enable caching for specific API calls using the `cache()` method. The method accepts:
- `minutes`: How long to cache the response (in minutes)
- `cacheKey`: (Optional) Custom cache key. If not provided, one will be generated automatically

#### Example: Caching Exchange Configuration

```php
// Cache the exchange configuration for 60 minutes
$config = $taler->cache(60)
    ->exchange()
    ->getConfig();

// Use a custom cache key
$config = $taler->cache(60, 'exchange_config')
    ->exchange()
    ->getConfig();

// Subsequent calls within the TTL will return cached data
$cachedConfig = $taler->exchange()->getConfig();

// Force delete cached data
$taler->cacheDelete('exchange_config');
```

### Cache Implementation Requirements

If you want to implement your own cache, it must implement `Psr\SimpleCache\CacheInterface`. 

---

## Running Tests

To run the test suite:

```
composer install
php vendor/bin/phpunit
```

---

## Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

---

## Security Note: SSRF Risk

The TalerPHP SDK allows developers to make HTTP requests to arbitrary endpoints based on configuration and API usage. **If your application accepts or processes user-provided endpoints, you should be aware of the risk of Server-Side Request Forgery (SSRF).**

---

## License

This project is licensed under the MIT License. See [LICENSE](LICENSE) for details.

---

## Support

If you have questions or need help, open an issue or start a discussion on the repository.

---

## Acknowledgments

- [GNU Taler](https://taler.net/)
- All contributors and the open source community

---
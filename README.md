# TalerPHP

TalerPHP is a PHP SDK for interacting with GNU Taler payment systems. It provides a simple, secure way to integrate Taler payments into your PHP applications and services.

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

## Supported Taler protocol versions

- Supported Taler protocol versions: v12-v20
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
use Taler\Factory\Factory;

$taler = Factory::create([
    'base_url' => 'https://backend.demo.taler.net/instances/sandbox',
    'token'    => 'Bearer token'
]);

```

### Factory-managed authentication (no pre-existing token)
You can let the SDK obtain and manage the access token for you by providing credentials and the target instance. The SDK will:
- Request an access token using Basic auth
- Store the resulting `Authorization` header value internally
- Refresh the token automatically before it expires when sending requests

```php
use Taler\Factory\Factory;

$taler = Factory::create([
    'base_url' => 'https://backend.demo.taler.net/instances/sandbox',
    'username' => 'merchant-user',
    'password' => 'merchant-pass',
    'instance' => 'shop-1',         // instance ID
    // optional (defaults shown):
    'scope' => 'readonly',          // "readonly"|"write"|"all"|"order-simple"|"order-pos"|"order-mgmt"|"order-full"
    'duration_us' => 3600_000_000,  // token validity upper bound in microseconds or "forever"
    'description' => 'Backoffice session'
]);

// Use the client as usual; the SDK injects/refreshes the Authorization token automatically.
$orders = $taler->order()->getOrders(['limit' => '-20']);
```
#### Retrieve and persist the managed token

After creation, you can read the in-memory token from the client’s config and save it for reuse:

```php
// Extract the token and its expiry from the managed-auth client
$token = $taler->getConfig()->getAuthToken();                // e.g., "Bearer abc..."
$expiresAt = $taler->getConfig()->getAuthTokenExpiresAtTs(); // int|null (seconds)

// Persist to your storage (DB/Secrets Manager/etc.)
saveTokenSomewhere($token, $expiresAt);
```

Later, restore the client with the saved token:

```php
$taler = \Taler\Factory\Factory::create([
    'base_url' => 'https://backend.demo.taler.net/instances/sandbox',
    'token'    => $tokenFromStorage // already includes the "Bearer " prefix
]);
```

Note: The token string already includes the required “Bearer ” prefix; store and reuse it as-is.
Notes:
- If `token` is provided, it takes precedence and credentials are ignored.
- The password is only used to acquire a token; the SDK stores the resulting access token and its expiry metadata for refresh.

---

## Configuration

- `base_url`: The URL of your Taler backend instance.
- `token`: Your authentication token ( ⚠️ do **not** hardcode; use environment variables or secure storage in your application).
- `username` (optional): Merchant instance username for Factory-managed authentication.
- `password` (optional): Merchant instance password for Factory-managed authentication.
- `instance` (optional): Target instance ID to authenticate against when using credentials.
- `scope` (optional): Desired token scope when using credentials. One of `"readonly"|"write"|"all"|"order-simple"|"order-pos"|"order-mgmt"|"order-full"`. Defaults to `"readonly"`.
- `duration_us` (optional): Upper bound on token validity as microseconds (int) or `"forever"`. The server may override. Defaults to SDK/server defaults.
- `description` (optional): Human-readable description attached to the issued token.
- `wrapResponse`: (Optional) Boolean flag to control DTO wrapping of responses. Defaults to `true`. When set to `false`, methods return raw array responses from Taler.
- `httpClient`: (Optional) A PSR-18 compatible HTTP client instance.
- `logger`: (Optional) A PSR-3 compatible logger. If omitted, the SDK performs no logging.
- `debugLoggingEnabled`: (Optional) Boolean flag to enable SDK DEBUG logging. Defaults to `false`. When `false`, the SDK skips all debug logging work (zero overhead).

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
$httpClient = GuzzleAdapter::createWithConfig([
    // Strongly recommended: always set sane timeouts
    // Overall request timeout (seconds)
    'timeout' => 10.0,
    // Connection establishment timeout (seconds)
    'connect_timeout' => 5.0,

    // Redirect policy: prevent protocol downgrade and limit hops
    'allow_redirects' => [
        'max' => 3,                 // limit redirect chain length
        'protocols' => ['https'],   // disallow downgrade to http
        'referer' => false
    ],
]);

$taler = Factory::create([
    'base_url' => 'https://backend.demo.taler.net/instances/sandbox',
    'token'    => 'Bearer token',
    'client' => $httpClient
]);
```

### Security: Timeouts and Redirect Policies (Important)

- Request timeouts (HIGH):
  - Always inject a PSR-18 client configured with explicit timeouts. Without timeouts, network calls may hang indefinitely under network partitions or server issues, causing thread/worker exhaustion and cascading failures.
  - At minimum set both a connection timeout and a total request timeout. Example with Guzzle adapter above uses `connect_timeout` and `timeout`.

- Redirect handling (MEDIUM):
  - Enforce an upper bound on redirect chains to avoid redirect loops and reduce SSRF blast radius. Example above uses `allow_redirects.max`.
  - Disallow protocol downgrades to `http` by restricting `allow_redirects.protocols` to `['https']`. This prevents leaking credentials or tokens over plaintext during redirects.

Notes:
- PSR-18 does not define a standard for these options; use your chosen client's native configuration (e.g., Guzzle options). When using other clients, consult their documentation to apply equivalent settings (timeouts and redirect limits/HTTPS-only redirects).

---

## Backend validation and protocol versioning

When you create a `Taler` instance via the `Factory`, the SDK proactively validates that your backend is a Merchant backend and performs a protocol version compatibility check:

- Merchant backend check (fail-fast): The `Factory` calls `GET /config` and validates `name === "taler-merchant"`. If not, it throws `InvalidArgumentException` during creation.
- Protocol version triplet parsing: The SDK parses the version string `current:revision:age` from `/config` and compares it against the SDK's client current version (`Taler::TALER_PROTOCOL_VERSION`, currently 20).
  - Compatibility rule: clientCurrent must be within `[serverCurrent - serverAge, serverCurrent]`.
  - If out of range, the SDK logs a WARNING via your PSR-3 logger (if provided) to help you detect potential incompatibilities early. Example log message includes server version and the supported range.

Optional helpers for custom checks:

```php
use function Taler\Helpers\parseLibtoolVersion;      // returns [current, revision, age] or null
use function Taler\Helpers\isProtocolCompatible;      // boolean

$parsed = parseLibtoolVersion('20:0:8'); // [20, 0, 8]
[$serverCurrent, , $serverAge] = $parsed;
$ok = isProtocolCompatible($serverCurrent, $serverAge, (int) Taler::TALER_PROTOCOL_VERSION);
```

Notes:
- To receive the WARNING log, pass a PSR-3 logger to the `Factory`.
- This check is non-fatal (only logs) as long as the backend `name` is valid; the exception is thrown only when the backend is not a merchant backend.


---

##  Payment processing (Order API)
https://docs.taler.net/core/api-merchant.html#payment-processing

The Order API provides functionality to interact with Taler order services. Here's how to use it:

### Basic Setup

```php
use Taler\Factory\Factory;

$taler = Factory::create([
    'base_url' => 'https://backend.demo.taler.net/instances/sandbox',
    'token'    => 'Bearer token'
]);

$orderClient = $taler->order();
```

### Available Methods

#### Create Order

Create a new order using either a fixed-amount contract (`OrderV0`) or a choice-based contract (`OrderV1`). The call returns a `PostOrderResponse` with the generated `order_id`.

Minimal example with `OrderV0` (fixed amount):

```php
use Taler\Api\Order\Dto\OrderV0;
use Taler\Api\Order\Dto\PostOrderRequest;

$order = new OrderV0(
    summary: 'Coffee Beans 1kg',
    amount: 'EUR:12.50'
);

$request = new PostOrderRequest(order: $order);

// Create the order 
$result = $orderClient->createOrder($request);

// Access response
echo $result->order_id; // e.g., "order_123"
```

Example with `OrderV1` (choices):

```php
use Taler\Api\Order\Dto\OrderV1;
use Taler\Api\Order\Dto\OrderChoice;
use Taler\Api\Order\Dto\PostOrderRequest;

$order = new OrderV1(
    summary: 'Monthly Subscription',
    choices: [
        new OrderChoice(amount: 'EUR:9.99')
    ]
);

$request = new PostOrderRequest(
    order: $order,
);

$result = $orderClient->createOrder($request);

echo $result->order_id;
```

#### Handle errors for Create Order

When creating an order, the backend may return non-200 status codes for normal error conditions. The SDK throws typed exceptions to help you handle these cases with structured DTOs:

```php
use Taler\Api\Order\Dto\OrderV0;
use Taler\Api\Order\Dto\PostOrderRequest;
use Taler\Exception\OutOfStockException;              // HTTP 410
use Taler\Exception\PaymentDeniedLegallyException;    // HTTP 451
use Taler\Exception\TalerException;

$order = new OrderV0(
    summary: 'Coffee Beans 1kg',
    amount: 'EUR:12.50'
);

$request = new PostOrderRequest(order: $order);

try {
    $response = $orderClient->createOrder($request);
    // $response is PostOrderResponse on success
    echo $response->order_id . "\n";
} catch (OutOfStockException $e) { //--- http status code 410 Gone
    $dto = $e->getResponseDTO();
    if ($dto !== null) {
        // Access structured out-of-stock details
        // $dto->product_id (string)
        // $dto->requested_quantity (int)
        // $dto->available_quantity (int)
        // $dto->restock_expected?->t_s (int|string)
    }
    // Recover: show alternative products, adjust quantity, etc.

} catch (PaymentDeniedLegallyException $e) {  //--- http status code 451 Unavailable For Legal Reasons
    $dto = $e->getResponseDTO();
    if ($dto !== null) {
        // Exchanges that denied payment
        // $dto->exchange_base_urls is array<int, string>
    }
    // Recover: refresh coins from these exchanges or retry with others
} catch (TalerException $e) {
    // Other Taler API errors (e.g., 404/409). Inspect structured error details:
    // $error = $e->getResponseDTO(); // instance of Taler\Api\Dto\ErrorDetail or null
    // Or raw array JSON if preferred: $e->getResponseJson();
    throw $e;
} catch (\Throwable $e) {
    // Transport/runtime errors
    throw $e;
}
```

With custom headers or raw array response:

```php
// Custom headers
$result = $orderClient->createOrder($request, [
    'X-Custom-Header' => 'value'
]);

// Disable DTO wrapping to get raw array
$result = $taler->config(['wrapResponse' => false])
    ->order()
    ->createOrder($request);
```

#### Get Order Status

Retrieve the status and details of a specific order:

```php
// Get order by ID
$order = $orderClient->getOrder('order_123');

// The response type depends on the order status:
if ($order instanceof CheckPaymentPaidResponse) {
    // Order is paid
    echo $order->order_status;        // "paid"
    echo $order->deposit_total;       // Total amount deposited
    echo $order->refunded;            // Whether order was refunded
    echo $order->refund_pending;      // Whether refund is pending
    echo $order->wired;               // Whether funds were wired
    echo $order->refund_amount;       // Total refunded amount
    
    // Access contract terms
    $terms = $order->contract_terms;
    echo $terms->summary;             // Order summary
    echo $terms->order_id;            // Order ID
    
    // Access last payment timestamp
    echo $order->last_payment->t_s;   // Unix timestamp
} 

if ($order instanceof CheckPaymentClaimedResponse) {
    // Order is claimed but not paid
    echo $order->order_status;        // "claimed"
    echo $order->order_status_url;    // Status URL for browser/wallet
    
    // Contract terms
    $terms = $order->contract_terms;
    echo $terms->summary;             // Order summary
    echo $terms->order_id;            // Order ID
}

if ($order instanceof CheckPaymentUnpaidResponse) {
    // Order is neither claimed nor paid
    echo $order->order_status;        // "unpaid"
    echo $order->taler_pay_uri;       // URI for wallet to process payment
    echo $order->summary;             // Order summary
    echo $order->total_amount;        // Total amount to pay (may be null for v1)
    echo $order->order_status_url;    // Status URL for browser/wallet
    
    // Access creation timestamp
    echo $order->creation_time->t_s;  // Unix timestamp
}

// Get order with additional parameters
$order = $orderClient->getOrder('order_123', [
    'session_id' => 'session_xyz'     // Optional session ID
]);

// Get order with custom headers
$order = $orderClient->getOrder(
    orderId: 'order_123', 
    headers:[
        'X-Custom-Header' => 'value'
    ]
);
```

#### Get Orders History

Retrieve the order history with optional filtering:

```php
// Get orders (default limit: 20 asc)
$orders = $orderClient->getOrders();

// Get orders with filters
$orders = $orderClient->getOrders([
    'limit' => '-20',  // last 20 orders
]);

// Access order history details
foreach ($orders->orders as $order) {
    echo $order->order_id;    // Order ID of the transaction related to this entry
    echo $order->row_id;      // Row ID of the order in the database
    echo $order->amount;      // The amount of money the order is for
    echo $order->summary;     // The summary of the order
    echo $order->refundable;  // Whether the order can be refunded
    echo $order->paid;        // Whether the order has been paid or not
    
    // Access timestamp (Unix timestamp)
    echo $order->timestamp->t_s; // When the order was created
}
```

#### Refund Order

Initiate a refund for a specific order:

```php
use Taler\Api\Order\Dto\RefundRequest;

// Create refund request
$refundRequest = new RefundRequest(
    refund: 'EUR:10.00',  // Amount to be refunded
    reason: 'Customer request'  // Human-readable refund justification
);

// Initiate refund
$refund = $orderClient->refundOrder('order_123', $refundRequest);

// Access refund response details
echo $refund->taler_refund_uri;  // URL for wallet to process refund
echo $refund->h_contract;        // Contract hash for request authentication
```

#### Handle errors for Refund Order

The backend may deny a refund for legal reasons (451). The SDK raises a typed exception with structured data:

```php
use Taler\Api\Order\Dto\RefundRequest;
use Taler\Exception\PaymentDeniedLegallyException; // HTTP 451
use Taler\Exception\TalerException;

$refundRequest = new RefundRequest(
    refund: 'EUR:10.00',
    reason: 'Customer request'
);

try {
    $refund = $orderClient->refundOrder('order_123', $refundRequest);
    echo $refund->taler_refund_uri;
} catch (PaymentDeniedLegallyException $e) { // 451 Unavailable For Legal Reasons
    $dto = $e->getResponseDTO();
    if ($dto !== null) {
        // $dto->exchange_base_urls (array<int, string>)
    }
    // Recover: refresh coins from listed exchanges or retry via others
} catch (TalerException $e) {
    // Other API errors
    throw $e;
} catch (\Throwable $e) {
    // Transport/runtime errors
    throw $e;
}
```

#### Delete Order

Delete a specific order:

```php
// Delete an order by ID
$orderClient->deleteOrder('order_123');
```

#### Forget Order

Request the backend to forget specific fields of an order's contract terms.

Notes:
- The request uses HTTP PATCH and returns no content on success (HTTP 200 or 204).
- A valid JSON path must begin with `$.` and end with a field identifier. Array indices and wildcards `*` are allowed inside the path, but the path cannot end with an index or wildcard.

```php
use Taler\Api\Order\Dto\ForgetRequest;

// Create forget request
$forgetRequest = new ForgetRequest([
    '$.wire_fee',
    '$.products[0].description',
    // Wildcards allowed as long as the path ends with a field
    '$.products[*].description'
]);

// Send forget request (void on success)
$orderClient->forgetOrder('order_123', $forgetRequest);

// With custom headers
$orderClient->forgetOrder(
    orderId: 'order_123',
    forgetRequest: $forgetRequest,
    headers: [
        'X-Custom-Header' => 'value'
    ]
);
```

If the order or paths are invalid, or if the backend rejects the request (for example HTTP 400, 404, or 409), a `TalerException` will be thrown. The exception code contains the HTTP status, and you can inspect structured error details via `$e->getResponseDTO()` if the backend returned a JSON error body.

### Asynchronous Operations

All methods support asynchronous operations with the Async suffix:

```php
// Get orders asynchronously
$ordersPromise = $orderClient->getOrdersAsync();

// Handle the promise
$ordersPromise->then(function ($orders) {
    // Handle orders response
});
```

### Error Handling

The Order API methods may throw exceptions that you should handle:

```php
use Taler\Exception\TalerException;

try {
    $orders = $orderClient->getOrders();
} catch (TalerException $e) {
    // Handle Taler-specific errors
    echo $e->getMessage();
} catch (\Throwable $e) {
    // Handle other errors
    echo $e->getMessage();
}
```

#### Inspect structured error details (TalerException::getResponseDTO)

When the backend returns a JSON error response, `TalerException::getResponseDTO()` parses it into an `ErrorDetail` DTO for convenient, typed access.

```php
use Taler\Exception\TalerException;
use Taler\Api\Dto\ErrorDetail;

try {
    $orders = $orderClient->getOrders();
} catch (TalerException $e) {
    /** @var ErrorDetail|null $err */
    $err = $e->getResponseDTO();
    if ($err !== null) {
        // Numeric error code unique to the condition
        echo $err->code . "\n";
        // Human-readable hint from the server (optional)
        echo ($err->hint ?? '') . "\n";
        // Additional optional fields may be present depending on the API
        // e.g., $err->detail, $err->parameter, $err->path, $err->extra
    }

    // Fallback to raw array if needed
    // $json = $e->getResponseJson();
}
```

### Response Types

By default, responses are wrapped in DTOs (`OrderHistory` and its nested objects). You can configure this behavior:

```php
// Disable DTO wrapping to get raw array responses
$orders = $taler->config(['wrapResponse' => false])->order()->getOrders();

/*
Array response example:
[
    'orders' => [
        [
            'order_id' => 'order_123',
            'row_id' => 1,
            'timestamp' => ['t_s' => 1234567890],
            'amount' => '10.00',
            'summary' => 'Order description',
            'refundable' => true,
            'paid' => true
        ],
        // ... more orders
    ]
]
*/

// Raw array response for refunds
$refund = $taler->config(['wrapResponse' => false])->order()->refundOrder(
    'order_123',
    new RefundRequest('10.00', 'Customer request')
);

/*
Array response example:
[
    'taler_refund_uri' => 'taler://refund/...',
    'h_contract' => 'hash_value'
]
*/
```

---

## Config API

### Basic Setup

```php
use Taler\Factory\Factory;

$taler = Factory::create([
    'base_url' => 'https://backend.demo.taler.net',
    'token'    => 'Bearer token'
]);

$configClient = $taler->configApi();
```

### Get Merchant Config

```php
$config = $taler->configApi()->getConfig(); // MerchantVersionResponse

// Core fields
echo $config->version;   // e.g., "42:1:0" (libtool current:revision:age)
echo $config->name;      // always "taler-merchant"
echo $config->currency;  // default currency, e.g., "EUR"

// Currency specifications (map of code => CurrencySpecification)
foreach ($config->currencies as $code => $spec) {
    echo $code;                         // e.g., "EUR"
    echo $spec->name;                   // e.g., "Euro"
    echo $spec->alt_unit_names['0'];    // base symbol/name, e.g., "€"
}

// Trusted exchanges
foreach ($config->exchanges as $ex) { // array of ExchangeConfigInfo
    echo $ex->base_url;  // e.g., "https://exchange.example.com"
    echo $ex->currency;  // e.g., "EUR"
    echo $ex->master_pub; // EddsaPublicKey as string
}

// Capabilities
echo $config->have_self_provisioning ? 'yes' : 'no'; // bool
echo $config->have_donau ? 'yes' : 'no';             // bool

// Optional TAN channels (array of strings: "sms", "email")
if ($config->mandatory_tan_channels !== null) {
    foreach ($config->mandatory_tan_channels as $ch) {
        echo $ch;
    }
}
```

### Credential Health Check

Quickly validate that your current `Taler` instance is properly configured and authenticated. This performs a minimal set of safe checks using the instance you already created:

- GET `/config` (always)
- If `token` is non-empty: GET `private` (instance exists and is reachable)
- If `token` is non-empty: GET `private/orders?limit=1` (auth-only harmless call)

```php
use Taler\Factory\Factory;

$taler = Factory::create([
    'base_url' => 'https://backend.demo.taler.net/instances/sandbox',
    'token'    => 'Bearer token'
]);

$diagnose = $taler->configCheck();

if ($diagnose['ok']) {
    // All checks passed – proceed with normal operations
} else {
    // Inspect failing step(s): 'config', 'instance', 'auth'
    // Each step contains: ok (bool), status (?int), error (?string), exception (?Throwable)
    $failed = array_filter([
        'config' => $diagnose['config'] ?? null,
        'instance' => $diagnose['instance'] ?? null,
        'auth' => $diagnose['auth'] ?? null,
    ], fn($x) => is_array($x) && ($x['ok'] ?? false) === false);

    // Example: log the most relevant failure
    if (!empty($failed)) {
        $first = reset($failed);
        error_log('Health check failed: ' . (($first['error'] ?? 'unknown') . ' (status=' . (($first['status'] ?? null) ?? 'n/a') . ')'));
        // Optionally inspect original exception object for details
        // $ex = $first['exception'] ?? null; // instance of Taler\\Exception\\TalerException or other Throwable
    }
}
```

Example report shape (abbreviated):

```
[
  'ok' => false,
  'config' => ['ok' => true, 'status' => 200, 'error' => null],
  'instance' => ['ok' => true, 'status' => 200, 'error' => null],
  'auth' => ['ok' => false, 'status' => 401, 'error' => 'unauthorized', 'exception' => TalerException],
]
```

Notes:
- If the `token` is empty, the instance/auth checks are skipped and `'ok'` reflects only the `/config` result.
- The `exception` field contains the original exception object for diagnostics; avoid serializing it directly. If you need a compact form, extract `class`, `code`, `message`, and optional response details.

With custom headers:

```php
$config = $taler->configApi()->getConfig([
    'X-Custom-Header' => 'value'
]);
```

Raw array response (disable DTO wrapping):

```php
$array = $taler
    ->config(['wrapResponse' => false])
    ->configApi()
    ->getConfig();

// Example shape (abbreviated):
// [
//   'version' => '42:1:0',
//   'currency' => 'EUR',
//   'currencies' => [ 'EUR' => [ 'name' => 'Euro', 'currency' => 'EUR', ... ] ],
//   'exchanges' => [ [ 'base_url' => 'https://exchange.example.com', 'currency' => 'EUR', 'master_pub' => '...' ] ],
//   'have_self_provisioning' => true,
//   'have_donau' => false,
//   'mandatory_tan_channels' => ['sms','email']
// ]
```

---

## Inventory

Reference: Merchant Backend Inventory API.

### Basic Setup

```php
use Taler\Factory\Factory;

$taler = Factory::create([
    'base_url' => 'https://backend.demo.taler.net/instances/sandbox',
    'token'    => 'Bearer token'
]);

$inventory = $taler->inventory();
```

### Categories

#### Get Categories

```php
$list = $inventory->getCategories(); // CategoryListResponse

foreach ($list->categories as $entry) {
    echo $entry->category_id;  // int
    echo $entry->name;         // string
    echo $entry->product_count;// int
}
```

#### Get Category

```php
$category = $inventory->getCategory(1); // CategoryProductList

echo $category->name; // string
foreach ($category->products as $p) {
    echo $p->product_id; // string
}
```

#### Create Category

```php
use Taler\Api\Inventory\Dto\CategoryCreateRequest;

$req = new CategoryCreateRequest(
    name: 'Beverages',
    name_i18n: ['de' => 'Getränke']
);

$created = $inventory->createCategory($req); // CategoryCreatedResponse
echo $created->category_id; // int
```

#### Update Category

```php
use Taler\Api\Inventory\Dto\CategoryCreateRequest;

$patch = new CategoryCreateRequest(
    name: 'Drinks'
);

$inventory->updateCategory(1, $patch); // 204 No Content on success
```

#### Delete Category

```php
$inventory->deleteCategory(1); // 204 No Content. May throw on not found.
```

### Products

#### Get Products

```php
use Taler\Api\Inventory\Dto\GetProductsRequest;

$req = new GetProductsRequest(limit: 20, offset: '10');
$summary = $inventory->getProducts($req); // InventorySummaryResponse

foreach ($summary->products as $entry) {
    echo $entry->product_id;    // string
    echo $entry->product_serial; // int
}
```

#### Get Product

```php
$product = $inventory->getProduct('coffee-1kg'); // ProductDetail

echo $product->product_name; // string
echo $product->price;        // Amount as string, e.g. "EUR:12.50"
```

#### Create Product

```php
use Taler\Api\Inventory\Dto\ProductAddDetail;
use Taler\Api\Dto\RelativeTime;

$details = new ProductAddDetail(
    product_id: 'coffee-1kg',
    description: 'Arabica beans 1kg',
    unit: 'kg',
    price: 'EUR:12.50',
    total_stock: 100,
    product_name: 'Coffee Beans',
);

$inventory->createProduct($details); // 204 No Content
```

#### Update Product

```php
use Taler\Api\Inventory\Dto\ProductPatchDetail;

$patch = new ProductPatchDetail(
    description: 'Arabica beans 1kg (fresh roast)',
    unit: 'kg',
    price: 'EUR:12.50',
    total_stock: 150
);

$inventory->updateProduct('coffee-1kg', $patch); // 204 No Content
```

#### Delete Product

```php
$inventory->deleteProduct('coffee-1kg'); // 204 No Content. 404 may be treated as no-op.
```

### POS Configuration

```php
$pos = $inventory->getPos(); // FullInventoryDetailsResponse

foreach ($pos->categories as $cat) {
    echo $cat->id . ':' . $cat->name . "\n";
}

foreach ($pos->products as $p) {
    echo $p->product_id . ' => ' . $p->price . "\n";
}
```

### Lock Product Quantity

Lock or unlock a quantity for a short duration.

```php
use Taler\Api\Inventory\Dto\LockRequest;
use Taler\Api\Dto\RelativeTime;

$lock = new LockRequest(
    lock_uuid: '123e4567-e89b-12d3-a456-426614174000',
    duration: new RelativeTime(60_000_000), // 60 seconds
    quantity: 2                              
);

$inventory->lockProduct('coffee-1kg', $lock); // 204 No Content
```

### Raw Array Response

Like other clients, you can disable DTO wrapping:

```php
$array = $taler
    ->config(['wrapResponse' => false])
    ->inventory()
    ->getProducts();
```

### Asynchronous Operations

All Inventory methods support asynchronous operations using the `Async` suffix (e.g., `getProductsAsync`, `getPosAsync`, `lockProductAsync`).

---

## Tracking Wire Transfers
Reference: [Merchant Backend: GET /instances/$INSTANCE/private/transfers](https://docs.taler.net/core/api-merchant.html#get-[-instances-$INSTANCE]-private-transfers)

The Wire Transfers API lets you list wire transfers credited to the merchant, optionally filtered via query parameters.

### Basic Setup

```php
use Taler\Factory\Factory;

$taler = Factory::create([
    'base_url' => 'https://backend.demo.taler.net/instances/sandbox',
    'token'    => 'Bearer token'
]);

$wireTransfers = $taler->wireTransfers();
```

### Get Transfers

```php
use Taler\Api\WireTransfers\Dto\GetTransfersRequest;

// Without filters (default server-side paging)
$list = $wireTransfers->getTransfers(); // TransfersList

foreach ($list->transfers as $transfer) {
    echo $transfer->credit_amount;      // e.g. "EUR:100.00"
    echo $transfer->wtid;               // Wire transfer identifier
    echo $transfer->payto_uri;          // Merchant payto URI
    echo $transfer->exchange_url;       // Exchange base URL
    echo $transfer->transfer_serial_id; // Serial ID
    echo $transfer->execution_time->t_s;// Unix timestamp
    echo $transfer->verified ? 'yes' : 'no';     // optional
    echo $transfer->confirmed ? 'yes' : 'no';    // optional
    echo $transfer->expected ? 'yes' : 'no';     // optional
}

// With filters
$request = new GetTransfersRequest(
    payto_uri: 'payto://iban/DE89370400440532013000?receiver-name=Example%20Merchant',
    after: '1700000000', // returns transfers after this timestamp (string per API)
    limit: 20,
);

$filtered = $wireTransfers->getTransfers($request, [
    'X-Custom-Header' => 'value'
]);
```

### Asynchronous

```php
$promise = $wireTransfers->getTransfersAsync();
$promise->then(function ($list) {
    // $list is TransfersList when wrapResponse is true
});
```

### Raw Array Response

```php
use Taler\Api\WireTransfers\Dto\GetTransfersRequest;

$req = new GetTransfersRequest(limit: 10);

$arrayResponse = $taler
    ->config(['wrapResponse' => false])
    ->wireTransfers()
    ->getTransfers($req);

// Example shape:
// [
//   'transfers' => [
//     [
//       'credit_amount' => 'EUR:10.00',
//       'wtid' => 'WTID...',
//       'payto_uri' => 'payto://...',
//       'exchange_url' => 'https://exchange.example.com',
//       'transfer_serial_id' => 123,
//       'execution_time' => ['t_s' => 1700000000],
//       'verified' => true,
//       'confirmed' => false,
//       'expected' => true
//     ]
//   ]
// ]
```

### Delete Transfer

Reference: [Merchant Backend: DELETE /instances/$INSTANCE/private/transfers/$TID](https://docs.taler.net/core/api-merchant.html#delete-[-instances-$INSTANCE]-private-transfers-$TID)

```php
// Delete by transfer serial ID (TID). 204 No Content on success.
$taler->wireTransfers()->deleteTransfer('123');

// With custom headers
$taler->wireTransfers()->deleteTransfer('123', [
    'X-Custom-Header' => 'value'
]);
```

Async variant:

```php
$promise = $taler->wireTransfers()->deleteTransferAsync('123');
$promise->then(function () {
    // Deleted
});
```

Errors raise `Taler\Exception\TalerException` (e.g., not found).

---

## Bank Accounts
https://docs.taler.net/core/api-merchant.html#bank-accounts

### Basic Setup

```php
use Taler\Factory\Factory;
use Taler\Api\BankAccounts\Dto\AccountAddDetails;
use Taler\Api\BankAccounts\Dto\BasicAuthFacadeCredentials;

$taler = Factory::create([
    'base_url' => 'https://backend.demo.taler.net/instances/sandbox',
    'token'    => 'Bearer token'
]);

$bankAccountClient = $taler->bankAccount();
```

### Create Bank Account

Minimal example with only a payto URI:

```php
$details = new AccountAddDetails(
    payto_uri: 'payto://iban/DE89370400440532013000?receiver-name=Example%20Merchant'
);

try {
    $response = $bankAccountClient->createAccount($details);
    echo "h_wire: {$response->h_wire}\n";
    echo "salt:   {$response->salt}\n";
} catch (\Taler\Exception\TalerException $exception) {
    // Handle API error
}
```

With facade URL and Basic credentials with error handling and debug:

```php
$details = new AccountAddDetails(
    payto_uri: 'payto://iban/DE89370400440532013000?receiver-name=Example%20Merchant',
    credit_facade_url: 'https://bank-facade.example.com/api',
    credit_facade_credentials: new BasicAuthFacadeCredentials('facade-user', 'facade-pass')
);

try {
    $response = $bankAccountClient->createAccount($details);
    echo "h_wire: {$response->h_wire}\n";
    echo "salt:   {$response->salt}\n";
    // dd($response); // if using Symfony VarDumper or similar
} catch (\Taler\Exception\TalerException $exception) {
    // dd($exception->getMessage(), $exception->getCode(), $exception->getRawResponseBody());
}
```

Error handling follows the same pattern as other APIs and may throw `Taler\Exception\TalerException`.

---

### Get Bank Accounts

Retrieve all bank accounts configured for the merchant instance. See docs: https://docs.taler.net/core/api-merchant.html#get-[-instances-$INSTANCE]-private-accounts

```php
try {
    $summary = $bankAccountClient->getAccounts(); // AccountsSummaryResponse

    foreach ($summary->accounts as $account) {
        echo $account->payto_uri . "\n"; // payto URI
        echo $account->h_wire . "\n";    // hash of wire details
        echo ($account->active ? 'active' : 'inactive') . "\n";
    }
} catch (\Taler\Exception\TalerException $exception) {
    // Handle API error
}
```

Raw array response (disable DTO wrapping):

```php
$summary = $taler
    ->config(['wrapResponse' => false])
    ->bankAccount()
    ->getAccounts();

// Example shape:
// [
//   'accounts' => [
//       ['payto_uri' => 'payto://iban/..', 'h_wire' => '...', 'active' => true],
//       // ...
//   ]
// ]
```

---
### Get Bank Account

Retrieve a specific bank account by its `h_wire`. See docs: https://docs.taler.net/core/api-merchant.html#get-[-instances-$INSTANCE]-private-accounts-$H_WIRE

```php
$hWire = 'your-h-wire-hash';

try {
    $account = $bankAccountClient->getAccount($hWire); // BankAccountDetail

    echo $account->payto_uri . "\n";           // full payto URI
    echo $account->h_wire . "\n";              // hash over wire details
    echo $account->salt . "\n";                // salt used to compute h_wire
    echo ($account->active ? 'active' : 'inactive') . "\n";
    echo ($account->credit_facade_url ?? '');    // optional
} catch (\Taler\Exception\TalerException $exception) {
    // Handle API error
}
```

### Update Bank Account

Update a specific bank account by its `h_wire`. Returns no content on success (HTTP 204). See docs: [PATCH /instances/$INSTANCE/private/accounts/$H_WIRE](https://docs.taler.net/core/api-merchant.html#patch-[-instances-$INSTANCE]-private-accounts-$H_WIRE)

Set or update the credit facade URL and credentials:

```php
use Taler\Api\BankAccounts\Dto\AccountPatchDetails;
use Taler\Api\BankAccounts\Dto\BasicAuthFacadeCredentials;

$hWire = 'your-h-wire-hash';

// Provide facade URL and Basic credentials
$patch = new AccountPatchDetails(
    credit_facade_url: 'https://bank-facade.example.com/api',
    credit_facade_credentials: new BasicAuthFacadeCredentials('facade-user', 'facade-pass')
);

// Apply update (204 No Content on success)
$bankAccountClient->updateAccount($hWire, $patch);
```

Remove stored facade credentials (preserving other fields):

```php
use Taler\Api\BankAccounts\Dto\AccountPatchDetails;
use Taler\Api\BankAccounts\Dto\NoFacadeCredentials;

$hWire = 'your-h-wire-hash';

$patch = new AccountPatchDetails(
    credit_facade_credentials: new NoFacadeCredentials()
);

$bankAccountClient->updateAccount($hWire, $patch);
```

Update only the facade URL (keep existing credentials):

```php
use Taler\Api\BankAccounts\Dto\AccountPatchDetails;

$hWire = 'your-h-wire-hash';

$patch = new AccountPatchDetails(
    credit_facade_url: 'https://bank-facade.example.com/v2'
);

$bankAccountClient->updateAccount($hWire, $patch);
```
### Delete Bank Account

Delete a specific bank account by its `h_wire`. Returns no content on success (HTTP 204). See docs: [DELETE /instances/$INSTANCE/private/accounts/$H_WIRE](https://docs.taler.net/core/api-merchant.html#delete-[-instances-$INSTANCE]-private-accounts-$H_WIRE)

```php
$hWire = 'your-h-wire-hash';

try {
    // 204 No Content on success
    $bankAccountClient->deleteAccount($hWire);
} catch (\Taler\Exception\TalerException $exception) {
    // Handle API error
}
```

With custom headers:

```php
$bankAccountClient->deleteAccount(
    hWire: 'your-h-wire-hash',
    headers: [
        'X-Custom-Header' => 'value'
    ]
);
```


### Asynchronous Operations

All Bank Accounts methods support asynchronous operations using the Async suffix:

```php
// Get bank accounts asynchronously
$accountsPromise = $bankAccountClient->getAccountsAsync();

// Handle the promise
$accountsPromise->then(function ($summary) {
    // $summary is an AccountsSummaryResponse when wrapResponse is true
    foreach ($summary->accounts as $account) {
        // Handle each bank account entry
        // $account->payto_uri, $account->h_wire, $account->active
    }
});
```

---
## OTP Devices

Reference: [Merchant Backend: POST /instances/$INSTANCE/private/otp-devices](https://docs.taler.net/core/api-merchant.html#post-[-instances-$INSTANCE]-private-otp-devices)

Create and register an OTP device to generate POS confirmations for orders.

### Basic Setup

```php
use Taler\Factory\Factory;

$taler = Factory::create([
    'base_url' => 'https://backend.demo.taler.net/instances/sandbox',
    'token'    => 'Bearer token'
]);

$otpDevices = $taler->otpDevices();
```

### Create OTP Device

The API accepts the algorithm as integer or string per the docs:
- 0 or "NONE": no algorithm
- 1 or "TOTP_WITHOUT_PRICE": without amounts (typical OTP device)
- 2 or "TOTP_WITH_PRICE": with amounts (special-purpose OTP device)

On success, the endpoint returns HTTP 204 No Content.

```php
use Taler\Api\OtpDevices\Dto\OtpDeviceAddDetails;

$details = new OtpDeviceAddDetails(
    otp_device_id: 'pos-device-1',
    otp_device_description: 'Main counter POS',
    otp_key: 'JBSWY3DPEHPK3PXP', // Base32-encoded secret
    otp_algorithm: 'TOTP_WITHOUT_PRICE' // or 0|1|2 or "NONE"|"TOTP_WITH_PRICE"
);

try {
    // 204 No Content on success
    $otpDevices->createOtpDevice($details);
    echo "OTP device created\n";
} catch (\Taler\Exception\TalerException $e) {
    // Handle Taler-specific API errors
    echo $e->getMessage();
} catch (\Throwable $e) {
    // Handle other errors
    echo $e->getMessage();
}
```

### Update OTP Device

Reference: [Merchant Backend: PATCH /instances/$INSTANCE/private/otp-devices/$DEVICE_ID](https://docs.taler.net/core/api-merchant.html#patch-[-instances-$INSTANCE]-private-otp-devices-$DEVICE_ID)

Update a registered OTP device. Returns HTTP 204 No Content on success.

```php
use Taler\Api\OtpDevices\Dto\OtpDevicePatchDetails;

$otpDevices = $taler->otpDevices();

// Minimal: update the description only (required)
$patch = new OtpDevicePatchDetails(
    otp_device_description: 'Front desk POS'
);

// Apply update (204 No Content on success)
$otpDevices->updateOtpDevice('pos-device-1', $patch);

// Optional: update key/algorithm/counter as well
// $patch = new OtpDevicePatchDetails(
//     otp_device_description: 'Front desk POS',
//     otp_key: 'JBSWY3DPEHPK3PXP',           // Base32-encoded secret
//     otp_algorithm: 'TOTP_WITH_PRICE',      // or 0|1|2 or "NONE"|"TOTP_WITHOUT_PRICE"
//     otp_ctr: 0
// );
// $otpDevices->updateOtpDevice('pos-device-1', $patch);
```

### Get OTP Device

Reference: [Merchant Backend: GET /instances/$INSTANCE/private/otp-devices/$DEVICE_ID](https://docs.taler.net/core/api-merchant.html#get-[-instances-$INSTANCE]-private-otp-devices-$DEVICE_ID)

Retrieve details of a specific OTP device. 

```php
use Taler\Api\OtpDevices\Dto\GetOtpDeviceRequest;

$otpDevices = $taler->otpDevices();

// Without query parameters
$device = $otpDevices->getOtpDevice('pos-device-1'); // OtpDeviceDetails

echo $device->device_description; // e.g., "Main counter POS"
echo $device->otp_algorithm;      // e.g., 1 or "TOTP_WITHOUT_PRICE"
echo $device->otp_timestamp;      // Unix timestamp (int)

// Optional fields (may be null)
echo $device->otp_ctr ?? '';
echo $device->otp_code ?? '';

// With query parameters and custom headers
$request = new GetOtpDeviceRequest(
    faketime: 1700000000,
    price: 'EUR:1.23'
);

$device = $otpDevices->getOtpDevice(
    deviceId: 'pos-device-1',
    request: $request,
    headers: [ 'X-Custom-Header' => 'value' ]
);
```

Raw array response (disable DTO wrapping):

```php
$array = $taler
    ->config(['wrapResponse' => false])
    ->otpDevices()
    ->getOtpDevice('pos-device-1');

// Example shape:
// [
//   'device_description' => 'Main counter POS',
//   'otp_algorithm' => 'TOTP_WITHOUT_PRICE', // or 0|1|2
//   'otp_timestamp' => 1700000000,           // int
//   'otp_ctr' => 0,                          // optional
//   'otp_code' => '123456'                   // optional
// ]
```

### Get OTP Devices

Reference: [Merchant Backend: GET /instances/$INSTANCE/private/otp-devices](https://docs.taler.net/core/api-merchant.html#get-[-instances-$INSTANCE]-private-otp-devices)

Retrieve all registered OTP devices for the instance.

```php
// Returns OtpDevicesSummaryResponse by default
$summary = $otpDevices->getOtpDevices();

foreach ($summary->otp_devices as $device) {
    echo $device->otp_device_id . "\n";      // e.g., "pos-device-1"
    echo $device->device_description . "\n";  // e.g., "Main counter POS"
}

// With custom headers
$summary = $otpDevices->getOtpDevices([
    'X-Custom-Header' => 'value'
]);
```

Raw array response (disable DTO wrapping):

```php
$summary = $taler
    ->config(['wrapResponse' => false])
    ->otpDevices()
    ->getOtpDevices();

// Example shape:
// [
//   'otp_devices' => [
//     [ 'otp_device_id' => 'device1', 'device_description' => 'Front desk POS' ],
//     [ 'otp_device_id' => 'device2', 'device_description' => 'Side counter POS' ],
//   ]
// ]
```

### Delete OTP Device

Reference: [Merchant Backend: DELETE /instances/$INSTANCE/private/otp-devices/$DEVICE_ID](https://docs.taler.net/core/api-merchant.html#delete-[-instances-$INSTANCE]-private-otp-devices-$DEVICE_ID)

```php
// Delete a specific OTP device by ID. 204 No Content on success.
$otpDevices->deleteOtpDevice('pos-device-1');

// With custom headers
$otpDevices->deleteOtpDevice('pos-device-1', [
    'X-Custom-Header' => 'value'
]);
```

### Asynchronous
All OTP Device methods support asynchronous operations using the Async suffix:
```php
use Taler\Api\OtpDevices\Dto\OtpDeviceAddDetails;

$details = new OtpDeviceAddDetails(
    otp_device_id: 'pos-device-2',
    otp_device_description: 'Side counter POS',
    otp_key: 'JBSWY3DPEHPK3PXP',
    otp_algorithm: 1 // TOTP_WITHOUT_PRICE
);

try {
    $promise = $otpDevices->createOtpDeviceAsync($details);
    // Promise resolves to null on 204
    $promise->wait();
    echo "OTP device created (async)\n";
} catch (\Taler\Exception\TalerException $e) {
    echo $e->getMessage();
} catch (\Throwable $e) {
    echo $e->getMessage();
}
```
## Templates

https://docs.taler.net/core/api-merchant.html#templates

### Basic Setup

```php
use Taler\Factory\Factory;

$taler = Factory::create([
    'base_url' => 'https://backend.demo.taler.net/instances/sandbox',
    'token'    => 'Bearer token'
]);

$templates = $taler->templates();
```

### Create Template

Returns no content on success (HTTP 204).

```php
use Taler\Api\Templates\Dto\TemplateAddDetails;
use Taler\Api\Templates\Dto\TemplateContractDetails;
use Taler\Api\Dto\RelativeTime;

$details = new TemplateAddDetails(
    template_id: 'invoice-2025',
    template_description: 'Default invoice template',
    template_contract: new TemplateContractDetails(
        minimum_age: 18,
        pay_duration: new RelativeTime(3600),
        summary: 'Service fee',
        currency: 'EUR',
        amount: 'EUR:10.00',
    ),
    otp_id: 'pos-device-1',
    editable_defaults: ['summary' => 'Editable']
);

// 204 No Content on success
$templates->createTemplate($details);
```

### Update Template

Returns no content on success (HTTP 204).

```php
use Taler\Api\Templates\Dto\TemplatePatchDetails;
use Taler\Api\Templates\Dto\TemplateContractDetails;
use Taler\Api\Dto\RelativeTime;

$patch = new TemplatePatchDetails(
    template_description: 'Updated description',
    template_contract: new TemplateContractDetails(
        minimum_age: 21,
        pay_duration: new RelativeTime(5400),
        summary: 'Updated service fee',
        currency: 'EUR',
        amount: 'EUR:12.00',
    ),
    otp_id: 'pos-device-2',
    editable_defaults: ['summary' => 'Editable']
);

// Apply update (204 No Content on success)
$templates->updateTemplate('invoice-2025', $patch);
```

### Get Templates

```php
$summary = $templates->getTemplates(); // TemplatesSummaryResponse by default

foreach ($summary->templates as $entry) {
    echo $entry->template_id . "\n";          // e.g., "invoice-2025"
    echo $entry->template_description . "\n"; // e.g., "Default invoice template"
}
```

Raw array response (disable DTO wrapping):

```php
$summaryArray = $taler
    ->config(['wrapResponse' => false])
    ->templates()
    ->getTemplates();

// Example shape:
// [ 'templates' => [ ['template_id' => 'invoice-2025', 'template_description' => '...'], ... ] ]
```

### Get Template

```php
$details = $templates->getTemplate('invoice-2025'); // TemplateDetails

echo $details->template_id;           // "invoice-2025"
echo $details->template_description;  // description

// Contract defaults
$contract = $details->template_contract;
echo $contract->summary;      // e.g., "Service fee"
echo $contract->currency;     // e.g., "EUR"
echo $contract->amount;       // e.g., "EUR:10.00"
echo $contract->minimum_age;  // e.g., 18
echo $contract->pay_duration->d_us; // microseconds or string representation

// Optional fields
echo $details->otp_id ?? '';
var_dump($details->editable_defaults ?? null);
```

Raw array response:

```php
$detailsArray = $taler
    ->config(['wrapResponse' => false])
    ->templates()
    ->getTemplate('invoice-2025');

// Example shape:
// [
//   'template_id' => 'invoice-2025',
//   'template_description' => '...',
//   'template_contract' => [ 'summary' => '...', 'currency' => 'EUR', 'amount' => 'EUR:10.00', 'minimum_age' => 18, 'pay_duration' => ['d_us' => 3600000000] ],
//   'otp_id' => 'pos-device-1',
//   'editable_defaults' => ['summary' => 'Editable']
// ]
```

### Delete Template

Returns no content on success (HTTP 204).

```php
$templates->deleteTemplate('invoice-2025');

// With custom headers
$templates->deleteTemplate('invoice-2025', [
    'X-Custom-Header' => 'value'
]);
```

### Asynchronous Operations

All Templates methods support asynchronous operations with the `Async` suffix:

```php
$promise = $templates->getTemplatesAsync();
$promise->then(function ($result) {
    // $result is TemplatesSummaryResponse when wrapResponse is true
});
```

---
## Token Families

The Token Families API lets you manage token families (discounts or subscriptions) for a merchant instance.

### Basic Setup

```php
use Taler\Factory\Factory;

$taler = Factory::create([
    'base_url' => 'https://backend.demo.taler.net/instances/sandbox',
    'token'    => 'Bearer token'
]);

$tokenFamilies = $taler->tokenFamilies();
```

### Create Token Family

Create a new token family. Returns no content on success (HTTP 204).

```php
use Taler\Api\TokenFamilies\Dto\TokenFamilyCreateRequest;
use Taler\Api\Dto\Timestamp;
use Taler\Api\Dto\RelativeTime;

$request = new TokenFamilyCreateRequest(
    slug: 'family-01',
    name: 'My Family',
    description: 'Human-readable description',
    description_i18n: ['en' => 'Human-readable description'],
    // For a discount family, use expected_domains; for a subscription family, use trusted_domains
    extra_data: ['expected_domains' => ['example.com']],
    valid_after: new Timestamp(1700000000),
    valid_before: new Timestamp(1800000000),
    // Ensure duration >= validity_granularity + start_offset
    duration: new RelativeTime(60_000_000),            // 1 minute
    validity_granularity: new RelativeTime(60_000_000),// 1 minute
    start_offset: new RelativeTime(0),
    kind: 'discount'                                   // or 'subscription'
);

// 204 No Content on success
$tokenFamilies->createTokenFamily($request);
```

### Update Token Family

Update an existing token family. Returns no content on success (HTTP 204).

```php
use Taler\Api\TokenFamilies\Dto\TokenFamilyUpdateRequest;
use Taler\Api\Dto\Timestamp;

$patch = new TokenFamilyUpdateRequest(
    name: 'Updated Name',
    description: 'Updated Description',
    description_i18n: ['en' => 'Updated Description'],
    // Depends on the token family kind; adjust accordingly
    extra_data: ['trusted_domains' => ['example.com']],
    valid_after: new Timestamp(1700000100),
    valid_before: new Timestamp(1800000100)
);

// 204 No Content on success
$tokenFamilies->updateTokenFamily('family-01', $patch);
```

### Get Token Families

List all configured token families for the instance. Returns `TokenFamiliesList` (HTTP 200).

```php
$list = $tokenFamilies->getTokenFamilies(); // TokenFamiliesList

foreach ($list->token_families as $family) {
    echo $family->slug . "\n";           // e.g., "family-01"
    echo $family->name . "\n";           // human-readable name
    echo $family->kind . "\n";           // "discount" or "subscription"
    echo $family->valid_after->t_s . "\n";// Unix timestamp
    echo $family->valid_before->t_s . "\n";// Unix timestamp
}
```

### Get Token Family

Get detailed information about a specific token family. Returns `TokenFamilyDetails` (HTTP 200).

```php
$details = $tokenFamilies->getTokenFamily('family-01'); // TokenFamilyDetails

echo $details->slug;                      // e.g., "family-01"
echo $details->name;                      // human-readable name
echo $details->description;               // description
echo $details->kind;                      // "discount" or "subscription"
echo $details->issued;                    // number of tokens issued
echo $details->used;                      // number of tokens used
echo $details->valid_after->t_s;          // Unix timestamp
echo $details->valid_before->t_s;         // Unix timestamp
echo $details->duration->d_us;            // microseconds
echo $details->validity_granularity->d_us;// microseconds
echo $details->start_offset->d_us;        // microseconds

// Optional
var_dump($details->description_i18n ?? null);
var_dump($details->extra_data ?? null);
```

### Delete Token Family

Delete a specific token family. Returns no content on success (HTTP 204).

```php
$tokenFamilies->deleteTokenFamily('family-01');

// With custom headers
$tokenFamilies->deleteTokenFamily('family-01', [
    'X-Custom-Header' => 'value'
]);
```

### Asynchronous Operations

Every Token Families method also supports an async variant using the `Async` suffix (e.g., `getTokenFamiliesAsync`, `getTokenFamilyAsync`, `createTokenFamilyAsync`, `updateTokenFamilyAsync`, `deleteTokenFamilyAsync`).

```php
// Example: Get token families asynchronously
$promise = $taler->tokenFamilies()->getTokenFamiliesAsync();

$promise->then(function ($result) {
    // $result is TokenFamiliesList when wrapResponse is true
});
```

---
## Webhooks

https://docs.taler.net/core/api-merchant.html#webhooks

### Basic Setup

```php
use Taler\Factory\Factory;

$taler = Factory::create([
    'base_url' => 'https://backend.demo.taler.net/instances/sandbox',
    'token'    => 'Bearer token'
]);

$webhooks = $taler->webhooks();
```

### Create Webhook

Returns no content on success (HTTP 204).

```php
use Taler\Api\Webhooks\Dto\WebhookAddDetails;

$details = new WebhookAddDetails(
    webhook_id: 'checkout-completed',
    event_type: 'ORDER_PAID',
    url: 'https://example.com/webhooks/order-paid',
    http_method: 'POST',
    header_template: '{"X-Webhook":"paid"}',  // optional
    body_template: '{"order_id":"$order_id"}' // optional
);

// 204 No Content on success
$webhooks->createWebhook($details);
```

### Update Webhook

Returns no content on success (HTTP 204).

```php
use Taler\Api\Webhooks\Dto\WebhookPatchDetails;

$patch = new WebhookPatchDetails(
    event_type: 'ORDER_PAID',
    url: 'https://example.com/webhooks/order-paid-v2',
    http_method: 'POST',
    header_template: '{"X-Webhook":"paid"}',   // optional
    body_template: '{"order_id":"$order_id"}'  // optional
);

$webhooks->updateWebhook('checkout-completed', $patch);
```

### Get Webhooks

```php
$summary = $webhooks->getWebhooks(); // WebhookSummaryResponse

foreach ($summary->webhooks as $entry) {
    echo $entry->webhook_id . "\n"; // e.g., "checkout-completed"
    echo $entry->event_type . "\n"; // e.g., "ORDER_PAID"
}

// With custom headers
$summary = $webhooks->getWebhooks([
    'X-Custom-Header' => 'value'
]);
```

### Get Webhook

```php
$details = $webhooks->getWebhook('checkout-completed'); // WebhookDetails

echo $details->event_type;     // e.g., "ORDER_PAID"
echo $details->url;            // e.g., "https://example.com/webhooks/order-paid"
echo $details->http_method;    // e.g., "POST"
echo $details->header_template ?? ''; // optional
echo $details->body_template ?? '';   // optional
```

### Delete Webhook

Returns no content on success (HTTP 204).

```php
$webhooks->deleteWebhook('checkout-completed');

// With custom headers
$webhooks->deleteWebhook('checkout-completed', [
    'X-Custom-Header' => 'value'
]);
```

### Asynchronous Operations

Every Webhooks method also supports an async variant using the `Async` suffix (e.g., `createWebhookAsync`, `updateWebhookAsync`, `getWebhooksAsync`, `getWebhookAsync`, `deleteWebhookAsync`).

```php
use Taler\Api\Webhooks\Dto\WebhookAddDetails;

$details = new WebhookAddDetails(
    webhook_id: 'checkout-completed',
    event_type: 'ORDER_PAID',
    url: 'https://example.com/webhooks/order-paid',
    http_method: 'POST'
);

$promise = $taler->webhooks()->createWebhookAsync($details);

// Promise resolves to null on 204 No Content
$promise->then(function () {
    echo "Webhook created (async)\n";
});
```

---

## Donau Charity

Reference: Merchant Backend Donau Charity endpoints.

### Basic Setup

```php
use Taler\Factory\Factory;

$taler = Factory::create([
    'base_url' => 'https://backend.demo.taler.net/instances/sandbox',
    'token'    => 'Bearer token'
]);

$donau = $taler->donauCharity();
```

### Get Linked Donau Charity Instances

```php
// Returns DonauInstancesResponse by default
$response = $donau->getInstances();

foreach ($response->donau_instances as $donau) {
    echo $donau->donau_instance_serial . "\n"; // int
    echo $donau->charity_name . "\n";          // string
    echo $donau->donau_url . "\n";             // string (base URL)
    echo $donau->charity_id . "\n";            // int
    echo $donau->charity_pub_key . "\n";       // EddsaPublicKey as string
    echo $donau->charity_max_per_year . "\n";  // Amount as string, e.g. "EUR:1000"
    echo $donau->charity_receipts_to_date . "\n"; // Amount as string
    echo $donau->current_year . "\n";          // int
}
```

Raw array response (disable DTO wrapping):

```php
$array = $taler
    ->config(['wrapResponse' => false])
    ->donauCharity()
    ->getInstances();
```

### Link (Create) a Donau Charity

Returns no content on success (HTTP 204). When the backend requires MFA (since v21), a Challenge is returned (HTTP 202).

```php
use Taler\Api\DonauCharity\Dto\PostDonauRequest;

$request = new PostDonauRequest(
    donau_url: 'https://donau.example', // https base URL of the Donau service
    charity_id: 7                       // numeric identifier within the Donau service
);

$challenge = $donau->createDonauCharity($request); // null on 204; Challenge on 202 (optional)
```

### Unlink (Delete) a Donau Charity by Serial

Returns no content on success (HTTP 204). If the serial does not exist, a `TalerException` is thrown.

```php
// Delete by Donau instance serial
$donau->deleteDonauCharityBySerial(321);

// With custom headers
$donau->deleteDonauCharityBySerial(321, [
    'X-Custom-Header' => 'value'
]);
```





## Instance Management

Manage merchant instances and access tokens. Reference: Merchant Backend Instance API.

### Basic Setup

```php
use Taler\Factory\Factory;

$taler = Factory::create([
    'base_url' => 'https://backend.demo.taler.net/instances/sandbox',
    'token'    => 'Bearer token'
]);

$instances = $taler->instance();
```

### Create Instance

```php
use Taler\Api\Instance\Dto\InstanceConfigurationMessage;
use Taler\Api\Instance\Dto\InstanceAuthConfigToken;
use Taler\Api\Dto\Location;
use Taler\Api\Dto\RelativeTime;

$config = new InstanceConfigurationMessage(
    id: 'shop-1',
    name: 'My Shop',
    auth: new InstanceAuthConfigToken(password: 'super-secret'),
    address: new Location(country: 'DE', town: 'Berlin'),
    jurisdiction: new Location(country: 'DE', town: 'Berlin'),
    use_stefan: true,
    default_wire_transfer_delay: new RelativeTime(3600_000_000), // 1 hour
    default_pay_delay: new RelativeTime(300_000_000),            // 5 minutes
    email: 'merchant@example.com'
);

// 204 No Content on success (no return value)
$instances->createInstance($config);
```

### Update Instance

```php
use Taler\Api\Instance\Dto\InstanceReconfigurationMessage;
use Taler\Api\Dto\Location;
use Taler\Api\Dto\RelativeTime;

$patch = new InstanceReconfigurationMessage(
    name: 'My Shop GmbH',
    address: new Location(country: 'DE', town: 'Berlin'),
    jurisdiction: new Location(country: 'DE', town: 'Berlin'),
    use_stefan: true,
    default_wire_transfer_delay: new RelativeTime(7200_000_000), // 2 hours
    default_pay_delay: new RelativeTime(600_000_000),             // 10 minutes
    website: 'https://shop.example.com'
);

// 204 No Content on success
$instances->updateInstance('shop-1', $patch);
```

### Get Instances

```php
$list = $instances->getInstances(); // InstancesResponse

foreach ($list->instances as $i) {
    echo $i->id . ' => ' . $i->name . "\n";
}
```

### Get Instance

```php
$details = $instances->getInstance('shop-1'); // QueryInstancesResponse

echo $details->name;                          // e.g., "My Shop GmbH"
echo $details->merchant_pub;                  // EddsaPublicKey
echo $details->default_pay_delay->d_us;       // microseconds
```

### Authentication & Access Tokens

Request a login token for an instance and list or revoke tokens.

```php
use Taler\Api\Instance\Dto\LoginTokenRequest;
use Taler\Api\Dto\RelativeTime;

// Request a login token (Authorization header value)
$req = new LoginTokenRequest(
    scope: 'order-full',
    duration: new RelativeTime(3_600_000_000),
    description: 'Backoffice session'
);

$token = $instances->getAccessToken('shop-1', $req); // LoginTokenSuccessResponse
echo $token->access_token;                            // RFC 8959 prefix included

// List issued tokens (latest first by default)
use Taler\Api\Instance\Dto\GetAccessTokensRequest;
$tokens = $instances->getAccessTokens('shop-1', new GetAccessTokensRequest(limit: -20));
if ($tokens !== null) {
    foreach ($tokens->tokens as $t) {
        echo $t->serial . ' ' . $t->scope . "\n";
    }
}

// Revoke the token presented in the Authorization header
$instances->deleteAccessToken('shop-1'); // 204 No Content

// Revoke a token by its serial number
$instances->deleteAccessTokenBySerial('shop-1', 123); // 204 No Content
```

Alternatively, you can let the SDK manage access tokens automatically via the Factory. Provide `username`, `password`, and `instance` to `Factory::create(...)` (optionally `scope`, `duration_us`, `description`) and the SDK will obtain and refresh the token for you. See “Factory-managed authentication” in the Usage section above.

### KYC Status

```php
use Taler\Api\Instance\Dto\GetKycStatusRequest;

$kyc = $instances->getKycStatus('shop-1', new GetKycStatusRequest(
    h_wire: 'H_WIRE_HASH',
    lpt: 3,
    timeout_ms: 30_000
));

if ($kyc !== null) {
    // MerchantAccountKycRedirectsResponse
    foreach ($kyc->kyc_redirects as $r) {
        echo $r->exchange_url . "\n";
    }
}
```

### Merchant Statistics

```php
use Taler\Api\Instance\Dto\GetMerchantStatisticsAmountRequest;
use Taler\Api\Instance\Dto\GetMerchantStatisticsCounterRequest;

$amounts = $instances->getMerchantStatisticsAmount('shop-1', 'ORDERS', new GetMerchantStatisticsAmountRequest(by: 'BUCKET'));
echo $amounts->by_bucket[0]->amount; // e.g., "EUR:10.00"

$counters = $instances->getMerchantStatisticsCounter('shop-1', 'VISITS', new GetMerchantStatisticsCounterRequest(by: 'INTERVAL'));
echo $counters->by_interval[0]->count; // integer
```

### Delete or Purge Instance

Disable or permanently purge an instance. When 2FA is required, a `Challenge` is returned (HTTP 202).

```php
// Disable instance (204 on success, or Challenge on 202)
$challenge = $instances->deleteInstance('shop-1');
if ($challenge instanceof Taler\Api\Instance\Dto\Challenge) {
    echo $challenge->getChallengeId();
}

// Purge instance (irreversible)
$instances->deleteInstance('shop-1', purge: true);
```

### Raw Array Responses

Like other clients, you can disable DTO wrapping:

```php
$array = $taler
    ->config(['wrapResponse' => false])
    ->instance()
    ->getInstances();
```

### Asynchronous Operations

All Instance methods also support asynchronous operations using the Async suffix.

```php
$promise = $taler->instance()->getInstancesAsync();
$promise->then(function ($result) {
    // $result is InstancesResponse when wrapResponse is true
});
```

---

## Two Factor Auth (2FA)

Certain protected operations (for example “update auth”, “change bank account”, or “delete instance”) may require a two-factor authentication. In such cases, the protected endpoint replies with HTTP 202 and a Challenge ID. You then:

1) Request transmission of a TAN for that Challenge
2) Confirm the Challenge with the received TAN
3) Repeat the original protected request with the exact same body and the `Taler-Challenge-Ids` header set to the Challenge ID

### Basic setup
```php
use Taler\Factory\Factory;
use Taler\Api\Instance\Dto\InstanceAuthConfigToken;
use Taler\Api\TwoFactorAuth\Dto\MerchantChallengeSolveRequest;

$taler = Factory::create([
    'base_url' => 'https://backend.demo.taler.net',
    'token'    => 'Bearer token'
]);

$instances = $taler->instance();
$twofa     = $taler->twoFactorAuth();
```

### 1) Protected call returns Challenge (example: update auth)
```php
// Example protected request that may require 2FA
$authConfig = new InstanceAuthConfigToken(password: 'new-secret');

// If 2FA is required, the method returns a Challenge (HTTP 202).
// If successful without 2FA, it returns null (HTTP 204).
$challenge = $instances->updateAuth('shop-1', $authConfig);

if ($challenge !== null) {
    $challengeId = $challenge->getChallengeId();
}
```

### 2) Request TAN for the challenge
```php
// The body must be a JSON object, but can be empty; the SDK sends {} by default.
$response = $twofa->requestChallenge('shop-1', $challengeId);

// $response is ChallengeRequestResponse with timing guidance:
// $response->solve_expiration->t_s
// $response->earliest_retransmission->t_s
```

### 3) Confirm the challenge with the TAN
```php
// TAN received out-of-band (e.g., SMS/email/UI input)
$tan = $merchantProvidedTan;
$solve = new MerchantChallengeSolveRequest($tan);
$twofa->confirmChallenge('shop-1', $challengeId, $solve); // 204 No Content on success
```

### 4) Repeat the original protected call with Taler-Challenge-Ids header
```php
// IMPORTANT: The repeated request must exactly match the original request body.
// Provide the challenge ID as a header when repeating the operation:
$instances->updateAuth(
    instanceId: 'shop-1',
    authConfig: $authConfig,
    headers: ['Taler-Challenge-Ids' => $challengeId]
); // 204 on success
```

Notes:
- Other protected operations (e.g., `deleteInstance`) follow the same pattern: try the operation, handle 202 Challenge, request TAN, confirm, then retry the operation with the `Taler-Challenge-Ids` header.
- All 2FA-related request/response DTOs use SDK-wide conventions (e.g., factory `createFromArray`, request DTOs validate, response DTOs do not validate).
- All actions also have methods suffixed with `Async` to run operations asynchronously.

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

// Initialize Taler with logger (and enable SDK debug logging)
$taler = Factory::create([
    'base_url' => 'https://backend.demo.taler.net/instances/sandbox',
    'token'    => 'Bearer token',
    'logger'   => $logger,
    'debugLoggingEnabled' => true
]);
```

### What Gets Logged

The SDK logs the following information:

- HTTP request details (URL, method, headers)
- HTTP response details (status code, sanitized headers, sanitized body preview)
- Error conditions and exceptions
- API operation failures

Notes:
- Logging is performed only when `debugLoggingEnabled` is `true`. Otherwise, the SDK does not execute any logging code paths (zero overhead).
- Request body logging is disabled.
- Response body logging is sanitized and truncated:
  - Secrets (e.g., Authorization tokens, access_token, api_key, client_secret, password) are redacted.
  - Sensitive headers such as Authorization, Cookie, and Set-Cookie are redacted.
  - URL userinfo (user:pass@) is redacted in logs.
  - Sensitive query parameters in URLs (e.g., `authorization`, `access_token`, `token`, `api_key`, `api-key`, `client_secret`, `password`, `pwd`, `merchant_sig`, `lpt`) are redacted.
  - URL-bearing headers like `Location` and `Content-Location` are sanitized (including query redaction).
  - Only a preview (up to ~4KB) is logged; non-seekable streams are skipped.

### Log Levels Used

- **DEBUG**: Detailed request/response information
- **ERROR**: API errors, request failures, and exceptions

Performance note:
- Enabling DEBUG logging increases overhead due to header redaction and response body sanitization. For large responses this can be noticeable. If you do not need logging, do not provide a logger and keep `debugLoggingEnabled` as `false` (default).

### Toggle at Runtime

You can enable or disable SDK debug logging at runtime:

```php
// Enable
$taler->config(['debugLoggingEnabled' => true]);

// Disable
$taler->config(['debugLoggingEnabled' => false]);
```

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

#### Example: Caching Merchant Backend Configuration

```php
// Cache the merchant backend configuration for 60 minutes
$config = $taler->cache(60)
    ->configApi()
    ->getConfig();

// Use a custom cache key
$config = $taler->cache(60, 'merchant_config')
    ->configApi()
    ->getConfig();

// Subsequent calls within the TTL will return cached data
$cachedConfig = $taler->configApi()->getConfig();

// Force delete cached data
$taler->cacheDelete('merchant_config');
```

### Cache Implementation Requirements

If you want to implement your own cache, it must implement `Psr\SimpleCache\CacheInterface`. 

---
## Security notes

- Do not enable DEBUG logging in production
  - Keep `debugLoggingEnabled` set to `false` in production. If you don’t need SDK logs, omit the `logger` entirely for zero overhead.

- Never include credentials in the base URL
  - Avoid userinfo (user:pass@) and secrets in query strings. Pass tokens via headers (e.g., `Authorization`) and configuration.

- Configure timeouts, TLS verification, and redirect policy in the injected HTTP client
  - Always set `timeout` and `connect_timeout`. Ensure TLS verification is ON (e.g., Guzzle `verify => true` or a CA bundle path). Limit redirects and restrict to HTTPS only (see example above).

- Avoid caching responses containing sensitive data
  - If you use the PSR-16 cache integration, do not cache endpoints that may include tokens. Prefer short TTLs and explicit cache keys; clear cached entries promptly when no longer needed.
---

## Using TalerPHP in WordPress

You can integrate the SDK into WordPress either from a theme or (recommended) from a dedicated plugin.

### Install the SDK with Composer

Install `mirrorps/taler-php` in a location that WordPress can autoload:

```bash
cd wp-plugins
mkdir taler-payments && cd taler-payments
composer require mirrorps/taler-php
```

Make sure your plugin (or theme) loads Composer’s autoloader:

```php
// In plugins/taler-payments/taler-payments.php
require_once __DIR__ . '/vendor/autoload.php';
```

If your Composer setup lives elsewhere (for example at the WordPress root), adjust the path accordingly (e.g. `require_once ABSPATH . '../vendor/autoload.php';`).

Store secrets such as the backend URL and access token in environment variables or `wp-config.php` (constants), not in your plugin code.

### Minimal plugin: shortcode-based “Pay with Taler” button

Create a plugin file at `plugins/taler-payments/taler-payments.php`:

```php
<?php
/**
 * Plugin Name: Taler Payments
 * Description: Simple integration of the TalerPHP SDK.
 * Version: 0.1.0
 */

if (! defined('ABSPATH')) {
    exit;
}

// Adjust the path if your composer.json is located elsewhere.
require_once __DIR__ . '/vendor/autoload.php';

use Taler\Factory\Factory;
use Taler\Api\Order\Dto\OrderV0;
use Taler\Api\Order\Dto\PostOrderRequest;
use Taler\Api\Order\Dto\CheckPaymentUnpaidResponse;

/**
 * Lazily create and reuse a Taler client.
 */
function taler_wp_client(): \Taler\Taler
{
    static $client = null;

    if ($client === null) {
        // Configure via environment variables or wp-config.php constants.
        $baseUrl = getenv('TALER_BASE_URL') ?: (defined('TALER_BASE_URL') ? TALER_BASE_URL : '');
        $token   = getenv('TALER_TOKEN') ?: (defined('TALER_TOKEN') ? TALER_TOKEN : '');

        $client = Factory::create([
            'base_url' => $baseUrl,
            'token'    => $token, // e.g. "Bearer abc..."
        ]);
    }

    return $client;
}

/**
 * Shortcode: [taler_pay_button amount="EUR:5.00" summary="Donation"]
 *
 * Renders a “Pay with GNU Taler” link that the wallet can use.
 */
function taler_wp_render_pay_button($atts): string
{
    $atts = shortcode_atts(
        [
            'amount'  => 'EUR:5.00',
            'summary' => 'Donation',
        ],
        $atts,
        'taler_pay_button'
    );

    $taler       = taler_wp_client();
    $orderClient = $taler->order();

    $order = new OrderV0(
        summary: sanitize_text_field($atts['summary']),
        amount: sanitize_text_field($atts['amount']),
        fulfillment_message: 'Thank you for your purchase. Your order will be fulfilled after payment.'
    );

    $request = new PostOrderRequest(order: $order);

    try {
        // 1) Create order and get its ID
        $created = $orderClient->createOrder($request);

        // 2) Fetch unpaid order status, including taler_pay_uri
        $status = $orderClient->getOrder($created->order_id);

        if ($status instanceof CheckPaymentUnpaidResponse && $status->taler_pay_uri !== null) {
            return sprintf(
                '<a href="%s" class="taler-pay-button">Pay with GNU Taler</a>',
                $status->taler_pay_uri
            );
        }

        return '<!-- Taler: order created but no unpaid status/pay URI available. -->';
    } catch (\Taler\Exception\TalerException $e) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            return '<!-- Taler error: ' . esc_html($e->getMessage()) . ' -->';
        }

        return '<!-- Taler payment temporarily unavailable. -->';
    } catch (\Throwable $e) {
        return '<!-- Taler runtime error. -->';
    }
}

add_shortcode('taler_pay_button', 'taler_wp_render_pay_button');
```

Usage inside posts, pages, or blocks:

```text
[taler_pay_button amount="EUR:12.50" summary="Coffee Beans 1kg"]
```

The shortcode:

- Creates a Taler order using the SDK’s Order API.
- Retrieves the unpaid status to obtain the `taler_pay_uri`.
- Renders a link that compatible Taler wallets can use to start the payment flow.
You can further adapt this example to:

- Redirect to a custom thank-you page after payment confirmation using the Order status API.
- Log or persist WordPress-side order metadata alongside the Taler `order_id`.
- Add styling for `.taler-pay-button` via your theme or plugin CSS.

---

## Using TalerPHP in Drupal

You can integrate the SDK into Drupal (9/10/11) via a small custom module that uses the Drupal service container.

### Install the SDK with Composer

From your Drupal project root (where `composer.json` lives):

```bash
cd /path/to/drupal
composer require mirrorps/taler-php
```

Drupal will automatically pick up the SDK through Composer’s autoloader; no manual `require` is needed.

Store secrets such as the backend URL and access token in environment variables (`.env`) or `settings.php`, not directly in module code.

### Create a minimal `taler_payments` module

Create the module directory:

```bash
mkdir -p web/modules/custom/taler_payments
```

Or, if your Drupal root is not `web/`, adjust the path accordingly (for example `modules/custom/taler_payments`).

#### 1) Module info file

Create `web/modules/custom/taler_payments/taler_payments.info.yml`:

```yaml
name: 'Taler Payments'
type: module
description: 'Simple integration of the TalerPHP SDK.'
core_version_requirement: '^9 || ^10 || ^11'
package: 'Payments'
```

#### 2) Service definition: shared Taler client

Create `web/modules/custom/taler_payments/taler_payments.services.yml`:

```yaml
services:
  taler_payments.client:
    class: Taler\Taler
    factory: ['Drupal\taler_payments\Factory\TalerClientFactory', 'create']

```
Now create the factory at `web/modules/custom/taler_payments/src/Factory/TalerClientFactory.php`:

```php
<?php

namespace Drupal\taler_payments\Factory;

use Taler\Factory\Factory;
use Taler\Taler;

/**
 * Factory for creating the shared Taler client used in Drupal.
 *
 * This reads configuration from environment variables so we don't rely on
 * Symfony-style %env()% placeholders in Drupal's container.
 */
final class TalerClientFactory {

  /**
   * Create the Taler client instance.
   */
  public static function create(): Taler {
    $baseUrl = getenv('TALER_BASE_URL') ?: 'https://backend.demo.taler.net/instances/sandbox';
    $token = getenv('TALER_TOKEN') ?: '';

    return Factory::create([
      'base_url' => $baseUrl,
      'token' => $token,
    ]);
  }

}
```

**Configure environment**

Make sure your web/PHP environment exports:

- `TALER_BASE_URL` – your Taler backend instance (e.g. sandbox)
- `TALER_TOKEN` – your bearer token for that instance, e.g. `Bearer <real-token>`

#### 3) Route for a “Pay with Taler” page

Create `web/modules/custom/taler_payments/taler_payments.routing.yml`:

```yaml
taler_payments.pay_page:
  path: '/taler/pay'
  defaults:
    _controller: '\Drupal\taler_payments\Controller\TalerPayController::pay'
    _title: 'Pay with GNU Taler'
  requirements:
    _permission: 'access content'
```

#### 4) Allow the `taler://` URI scheme

By default, Drupal considers only a few URI schemes safe; `taler://` is not one of them. Add a small `.module` file to allow it.

Create `web/modules/custom/taler_payments/taler_payments.module`:

```php
<?php

/**
 * @file
 * Hooks and callbacks for the Taler Payments module.
 */

/**
 * Implements hook_allowed_protocols_alter().
 *
 * Ensure Drupal treats the "taler" URI scheme as safe so that links to
 * taler://pay/... are not stripped or rewritten.
 */
function taler_payments_allowed_protocols_alter(array &$protocols): void {
  if (!in_array('taler', $protocols, TRUE)) {
    $protocols[] = 'taler';
  }
}

```

#### 5) Controller that uses the SDK

Create `web/modules/custom/taler_payments/src/Controller/TalerPayController.php`:

```php
<?php

namespace Drupal\taler_payments\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Taler\Taler;
use Taler\Api\Order\Dto\OrderV0;
use Taler\Api\Order\Dto\PostOrderRequest;
use Taler\Api\Order\Dto\CheckPaymentUnpaidResponse;

class TalerPayController extends ControllerBase {

  /**
   * @var \Taler\Taler
   */
  protected Taler $taler;

  public function __construct(Taler $taler) {
    $this->taler = $taler;
  }

  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('taler_payments.client')
    );
  }

  /**
   * Simple "Pay with GNU Taler" page.
   */
  public function pay(): array {
    $orderClient = $this->taler->order();

    $order = new OrderV0(
      summary: 'Donation',
      amount: 'KUDOS:5.00', // adjust to your backend configuration
      fulfillment_message: 'Thank you for your donation. Your order will be fulfilled after payment.'
    );

    $request = new PostOrderRequest(order: $order);

    try {
      // 1) Create order and get its ID.
      $created = $orderClient->createOrder($request);

      // 2) Fetch unpaid order status, including taler_pay_uri.
      $status = $orderClient->getOrder($created->order_id);

      if ($status instanceof CheckPaymentUnpaidResponse && $status->taler_pay_uri !== null) {
        // Render a direct taler:// link without going through Drupal's Url
        // validators, since taler:// is a special wallet URI.
        $uri = $status->taler_pay_uri;
        $safe_uri = htmlspecialchars($uri, ENT_QUOTES, 'UTF-8');

        $link = '<a href="' . $safe_uri . '" class="taler-pay-button">'
          . $this->t('Pay with GNU Taler')
          . '</a>';

        // Mark the link as safe, since it comes from a trusted Taler backend.
        return [
          '#markup' => Markup::create($link),
        ];
      }

      return [
        '#markup' => $this->t('Taler order created, but no unpaid status is available.'),
      ];
    }
    catch (\Taler\Exception\TalerException $e) {
      // In production, you might log this instead of showing details.
      return [
        '#markup' => $this->t('Taler payment temporarily unavailable.'),
      ];
    }
    catch (\Throwable $e) {
      return [
        '#markup' => $this->t('An unexpected error occurred.'),
      ];
    }
  }

}
```

#### 6) Enable and test

1. **Clear caches** (via Drush or UI):
  - UI: `Admin → Configuration → Development → Performance` and click **“Clear all caches”**.

2. **Enable the module**:
  - Go to `Admin → Extend`.
  - Enable **“Taler Payments”** (under “Payments”).

3. **Test the payment page**:
  - Visit:

     /taler/pay
        - With valid `TALER_BASE_URL`, `TALER_TOKEN`, and the correct `amount` currency:
     - The controller should:
       - Create an order via the TalerPHP SDK.
       - Fetch its unpaid status (including `taler_pay_uri`).
       - Render a **“Pay with GNU Taler”** link with a `taler://pay/...` URI, which a Taler wallet can use.

If you see an error like `Unexpected response status code: 401`, this indicates the Taler backend rejected the request (e.g. invalid token); double-check `TALER_TOKEN` and `TALER_BASE_URL` configuration.

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

## Funding

This project is funded through [NGI TALER Fund](https://nlnet.nl/taler), a fund established by [NLnet](https://nlnet.nl) with financial support from the European Commission's [Next Generation Internet](https://ngi.eu) program. Learn more at the [NLnet project page](https://nlnet.nl/project/TalerPHP).

[<img src="https://nlnet.nl/logo/banner.png" alt="NLnet foundation logo" width="20%" />](https://nlnet.nl)
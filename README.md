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
use Taler\Factory\Factory;

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
- The request uses HTTP PUT and returns no content on success (HTTP 200).
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

If the order or paths are invalid, a `TalerException` will be thrown.

Note: The delete operation returns no content on success (HTTP 204). If the order doesn't exist or can't be deleted, a `TalerException` will be thrown.

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

Update an existing token family. Returns detailed information (HTTP 200) as `TokenFamilyDetails`.

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

// Returns TokenFamilyDetails on HTTP 200
$details = $tokenFamilies->updateTokenFamily('family-01', $patch);
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
## Exchange API

The Exchange API provides functionality to interact with Taler exchange services. Here's how to use it:

### Basic Setup

```php
use Taler\Factory\Factory;

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
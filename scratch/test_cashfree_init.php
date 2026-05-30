<?php
require __DIR__ . '/../vendor/autoload.php';

use Cashfree\Cashfree;
use Cashfree\Model\CreateOrderRequest;
use Cashfree\Model\CustomerDetails;

// Initialize Cashfree client in sandbox mode
$cashfree = new Cashfree(
    Cashfree::$SANDBOX,
    "APP_ID_MOCK",
    "SECRET_KEY_MOCK"
);

$request = new CreateOrderRequest([
    'order_id' => 'order_' . uniqid(),
    'order_amount' => 10.00,
    'order_currency' => 'INR',
    'customer_details' => new CustomerDetails([
        'customer_id' => 'cust_123',
        'customer_phone' => '9999999999',
        'customer_email' => 'test@example.com',
        'customer_name' => 'Test User'
    ])
]);

try {
    $result = $cashfree->PGCreateOrder($request);
    echo "Result type: " . gettype($result) . "\n";
    print_r($result);
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    if (method_exists($e, 'getResponseBody')) {
        echo "Response body: " . $e->getResponseBody() . "\n";
    }
}

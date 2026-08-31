<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Enums\PaymentGateway;
use App\Models\User;
use App\Services\Payment\Gateways\PayUGatewayService;
use App\Services\Payment\PaymentService;

echo "=== PayU.in Integration End-to-End Test ===\n";

$key = config('payment.gateways.payu.key');
$salt = config('payment.gateways.payu.salt');
$clientId = config('payment.gateways.payu.client_id');
$clientSecret = config('payment.gateways.payu.client_secret');

echo "Key: {$key}\n";
echo "Salt: {$salt}\n";
echo "Client ID: {$clientId}\n";

// 1. Instantiate Service
$payuService = new PayUGatewayService();
$paymentService = app(PaymentService::class);

// 2. Resolve Driver
$driver = $paymentService->resolve(PaymentGateway::PAYU);
echo "Driver resolved: " . get_class($driver) . "\n";

// 3. Test Outbound SHA512 Hash Generation
$txnid = "TEST_TXN_" . time();
$amount = "500.00";
$productinfo = "Wallet Funding - Order #{$txnid}";
$firstname = "John";
$email = "john.doe@example.com";

$outboundHash = $payuService->generateRequestHash($key, $txnid, $amount, $productinfo, $firstname, $email, $salt);
echo "Generated Outbound SHA512 Hash: {$outboundHash}\n";

if (strlen($outboundHash) === 128) {
    echo "[SUCCESS] Outbound SHA512 hash generated correctly (128 hex chars).\n";
} else {
    echo "[FAIL] Unexpected hash length: " . strlen($outboundHash) . "\n";
}

// 4. Test Inbound SHA512 Reverse Hash Verification
$inboundParams = [
    'key' => $key,
    'txnid' => $txnid,
    'amount' => $amount,
    'productinfo' => $productinfo,
    'firstname' => $firstname,
    'email' => $email,
    'status' => 'success',
    'mihpayid' => '403993715535311234',
];

// Calculate matching reverse hash: sha512(SALT|status||||||udf5|udf4|udf3|udf2|udf1|email|firstname|productinfo|amount|txnid|key)
$reverseSequence = "{$salt}|{$inboundParams['status']}|||||||||||{$email}|{$firstname}|{$productinfo}|{$amount}|{$txnid}|{$key}";
$inboundParams['hash'] = strtolower(hash('sha512', $reverseSequence));

echo "Calculated Reverse SHA512 Hash: {$inboundParams['hash']}\n";

$isHashValid = $payuService->verifyResponseHash($inboundParams, $salt, $key);
echo "Response Hash Verification Result: " . ($isHashValid ? "[PASSED]" : "[FAILED]") . "\n";

// 5. Test Webhook DTO Normalization
$dto = $payuService->normalizeWebhookPayload('PAYMENT_SUCCESS', $inboundParams);
echo "Normalized Webhook DTO:\n";
print_r($dto);

if ($dto['provider'] === 'payu' && $dto['status'] === 'successful' && $dto['gateway_order_id'] === $txnid) {
    echo "[SUCCESS] PayU DTO normalization matches expected structure.\n";
} else {
    echo "[FAIL] DTO normalization failed.\n";
}

echo "=== PayU End-to-End Validation Complete ===\n";

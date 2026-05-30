<?php

namespace App\Contracts\Payments;

use App\Models\PaymentTransaction;

interface PaymentGatewayInterface
{
    /**
     * Initialize payment on the gateway provider.
     *
     * @param PaymentTransaction $transaction
     * @return array Raw initialization response from the gateway (e.g. order_id, SDK tokens)
     */
    public function initializePayment(PaymentTransaction $transaction): array;

    /**
     * Verify payment status using response parameters.
     *
     * @param PaymentTransaction $transaction
     * @param array $params
     * @return bool
     */
    public function verifyPayment(PaymentTransaction $transaction, array $params): bool;

    /**
     * Verify incoming webhook signature.
     *
     * @param string $payload Raw request body
     * @param string $signatureHeader Signature header value
     * @param string|null $timestamp Optional timestamp header value (required by some gateways like Cashfree)
     * @return bool
     */
    public function verifyWebhookSignature(string $payload, string $signatureHeader, ?string $timestamp = null): bool;

    /**
     * Normalize incoming webhook payload into a standardized structure.
     *
     * @param string $event Raw event name from provider
     * @param array $payload Raw webhook payload
     * @return array Standardized webhook DTO
     */
    public function normalizeWebhookPayload(string $event, array $payload): array;
}

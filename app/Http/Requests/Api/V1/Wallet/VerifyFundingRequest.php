<?php

namespace App\Http\Requests\Api\V1\Wallet;

use Illuminate\Foundation\Http\FormRequest;

class VerifyFundingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $transactionId = $this->route('transactionId');
        $transaction = \App\Models\PaymentTransaction::find($transactionId);

        if ($transaction && $transaction->gateway === \App\Enums\PaymentGateway::CASHFREE) {
            return [
                'cf_payment_id' => 'nullable|string',
                'cf_signature' => 'nullable|string',
            ];
        }

        return [
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ];
    }
}

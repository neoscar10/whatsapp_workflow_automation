<div>
    <x-ui.modal wire:model="show" title="Fund Wallet">
        <div id="modal-form-area">
            <form wire:submit.prevent="initializeFunding" class="space-y-6">
                <!-- Packages Grid & Rate Details -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <label class="text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Select Recharge Package</label>
                        <span class="text-[10px] text-slate-500 font-bold bg-slate-100 dark:bg-slate-800 px-2.5 py-1 rounded-full">
                            Min Recharge: ₹{{ number_format($minimumRecharge, 2) }}
                        </span>
                    </div>

                    @if($packages->isEmpty())
                        <div class="p-4 bg-yellow-50 dark:bg-yellow-950/20 text-yellow-800 dark:text-yellow-400 rounded-xl text-xs border border-yellow-100 dark:border-yellow-900/30 text-center">
                            No active recharge packages are currently available. Please contact support or try again later.
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($packages as $pkg)
                                <div 
                                    wire:click="$set('selectedPackageId', '{{ $pkg->id }}')"
                                    class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all hover:scale-[1.01] hover:shadow-sm active:scale-[0.99] select-none {{ $selectedPackageId === $pkg->id ? 'border-primary bg-primary/5 dark:bg-primary/10 ring-1 ring-primary' : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:border-slate-350 dark:hover:border-slate-700' }}"
                                >
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-baseline gap-2">
                                            <span class="text-xs text-slate-400 dark:text-slate-500 font-medium">Recharge Package</span>
                                            <span class="text-lg font-black text-slate-800 dark:text-white">₹{{ number_format($pkg->amount, 0) }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            @if($selectedPackageId === $pkg->id)
                                                <span class="material-symbols-outlined text-[20px] text-primary" data-icon="check_circle">check_circle</span>
                                            @else
                                                <span class="w-5 h-5 rounded-full border border-slate-300 dark:border-slate-700"></span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="mt-2 pt-2 border-t border-slate-100 dark:border-slate-800/60 flex flex-wrap gap-x-3 gap-y-1 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                                        <span>Text: <strong class="text-slate-700 dark:text-slate-200">₹{{ number_format($pkg->text_rate, 4) }}</strong></span>
                                        <span class="text-slate-300 dark:text-slate-700">•</span>
                                        <span>Utility Temp: <strong class="text-slate-750 dark:text-slate-200">₹{{ number_format($pkg->template_utility_rate, 4) }}</strong></span>
                                        <span class="text-slate-300 dark:text-slate-700">•</span>
                                        <span>Marketing Temp: <strong class="text-slate-750 dark:text-slate-200">₹{{ number_format($pkg->template_marketing_rate, 4) }}</strong></span>
                                        <span class="text-slate-300 dark:text-slate-700">•</span>
                                        <span>Auth Temp: <strong class="text-slate-750 dark:text-slate-200">₹{{ number_format($pkg->template_auth_rate, 4) }}</strong></span>
                                        <span class="text-slate-300 dark:text-slate-700">•</span>
                                        <span>Automation: <strong class="text-slate-750 dark:text-slate-200">₹{{ number_format($pkg->automation_rate, 4) }}</strong></span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('selectedPackageId')
                            <div class="text-xs text-red-500 font-bold mt-1">{{ $message }}</div>
                        @enderror
                    @endif
                </div>

                <!-- Payment Method Selection (Velzon-compatible card deck layout) -->
                <div class="space-y-3">
                    <label class="text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Choose Secure Payment Method</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <!-- Razorpay Card -->
                        <div 
                            class="relative flex flex-col p-4 rounded-xl border cursor-pointer transition-all hover:scale-[1.01] hover:shadow-md active:scale-[0.99] select-none {{ $gateway === 'razorpay' ? 'border-primary bg-primary/5 dark:bg-primary/10 ring-1 ring-primary' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800' }}"
                            wire:click="$set('gateway', 'razorpay')"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                                    <span class="text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-200">Razorpay</span>
                                </div>
                                @if($gateway === 'razorpay')
                                    <span class="material-symbols-outlined text-[16px] text-primary" data-icon="check_circle">check_circle</span>
                                @endif
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-normal">Pay seamlessly via UPI, Cards, NetBanking, and all major Indian mobile wallets.</p>
                        </div>

                        <!-- Cashfree Card -->
                        <div 
                            class="relative flex flex-col p-4 rounded-xl border cursor-pointer transition-all hover:scale-[1.01] hover:shadow-md active:scale-[0.99] select-none {{ $gateway === 'cashfree' ? 'border-primary bg-primary/5 dark:bg-primary/10 ring-1 ring-primary' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800' }}"
                            wire:click="$set('gateway', 'cashfree')"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-teal-500"></span>
                                    <span class="text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-200">Cashfree</span>
                                </div>
                                @if($gateway === 'cashfree')
                                    <span class="material-symbols-outlined text-[16px] text-primary" data-icon="check_circle">check_circle</span>
                                @endif
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-normal">Pay using credit/debit cards, robust UPI, fast netbanking, and pay-later operations.</p>
                        </div>
                    </div>
                </div>

                <!-- Footer / Action Buttons -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" class="px-6 py-2.5 text-sm font-bold text-slate-500 hover:text-slate-700 transition-colors" wire:click="$set('show', false)">Cancel</button>
                    <button 
                        type="submit" 
                        class="px-8 py-2.5 text-sm font-bold text-white bg-primary rounded-xl hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all flex items-center justify-center gap-2"
                        wire:loading.attr="disabled"
                        wire:target="initializeFunding"
                    >
                        <span wire:loading wire:target="initializeFunding" class="animate-spin size-4 border-2 border-white/30 border-t-white rounded-full" role="status" aria-hidden="true"></span>
                        <span>Proceed to Pay</span>
                    </button>
                </div>
            </form>
        </div>
    </x-ui.modal>

    <!-- Payment Gateway Checkout Integration Script Block -->
    @push('scripts')
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script src="https://sdk.cashfree.com/js/v3/cashfree.js"></script>
    <script>
        document.addEventListener('livewire:init', () => {
            // Razorpay Listener
            Livewire.on('launch-razorpay', (event) => {
                const data = event[0];
                const transactionId = data.transaction_id;
                const checkoutData = data.checkout_data;

                const options = {
                    key: checkoutData.key,
                    amount: checkoutData.amount,
                    currency: checkoutData.currency,
                    name: checkoutData.name,
                    description: checkoutData.description,
                    order_id: checkoutData.order_id,
                    prefill: checkoutData.prefill,
                    handler: async function (response) {
                        try {
                            const isMock = checkoutData.order_id && checkoutData.order_id.startsWith('order_mock_');
                            const payload = {
                                razorpay_payment_id: response.razorpay_payment_id || 'pay_mock_' + Math.random().toString(36).substring(7),
                                razorpay_order_id: response.razorpay_order_id || checkoutData.order_id,
                                razorpay_signature: response.razorpay_signature || (isMock ? 'valid_mock_signature' : '')
                            };

                            const verifyRes = await fetch(`/api/v1/wallet/fund/${transactionId}/verify`, {
                                method: 'POST',
                                credentials: 'include',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                                },
                                body: JSON.stringify(payload)
                            });

                            const result = await verifyRes.json();

                            if (!verifyRes.ok || !result.success) {
                                throw new Error(result.message || "Failed to verify payment on backend.");
                            }

                            Livewire.dispatch('payment-verified');

                        } catch (err) {
                            alert("Payment verification failed: " + err.message);
                        }
                    },
                    modal: {
                        ondismiss: function () {
                            alert("Checkout process was cancelled.");
                        }
                    },
                    theme: {
                        color: "#4f46e5"
                    }
                };

                const rzp = new Razorpay(options);
                rzp.open();
            });

            // Cashfree Listener
            Livewire.on('launch-cashfree', (event) => {
                const data = event[0];
                const checkoutData = data.checkout_data;

                const cashfree = Cashfree({
                    mode: checkoutData.environment === 'production' ? 'production' : 'sandbox'
                });

                console.log('Cashfree checkout data:', JSON.stringify(checkoutData));

                cashfree.checkout({
                    paymentSessionId: checkoutData.payment_session_id,
                    returnUrl: checkoutData.return_url,
                    redirectTarget: '_self'
                }).then(() => {
                    console.log("Cashfree checkout opened successfully for order: " + checkoutData.order_id);
                }).catch((err) => {
                    console.error("Cashfree checkout error:", err);
                    alert("Failed to launch Cashfree checkout: " + (err.message || JSON.stringify(err)));
                });
            });
        });
    </script>
    @endpush
</div>

<div>
    <x-ui.modal wire:model="show" title="Fund Wallet">
        <div id="modal-form-area">
            <form wire:submit.prevent="initializeFunding" class="space-y-6">
                <!-- Amount -->
                <div class="space-y-2">
                    <label for="fund-amount" class="text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Amount (INR)</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 font-semibold text-sm">₹</span>
                        <input 
                            type="number" 
                            id="fund-amount" 
                            class="w-full pl-10 pr-14 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/20 transition-all dark:text-white" 
                            placeholder="500" 
                            wire:model="amount" 
                            min="10"
                        />
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 font-semibold text-[10px] uppercase">INR</span>
                    </div>
                    @error('amount')
                        <div class="text-xs text-red-500 font-bold mt-1">{{ $message }}</div>
                    @enderror
                    <div class="text-[10px] text-slate-400 dark:text-slate-500 italic">Minimum funding amount is ₹10.</div>
                </div>

                <!-- Quick Select Selectors -->
                <div class="grid grid-cols-4 gap-2">
                    <button type="button" class="px-2 py-2 text-xs font-bold text-slate-600 bg-slate-100 dark:bg-slate-800 dark:text-slate-300 rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-all" wire:click="$set('amount', 100)">+₹100</button>
                    <button type="button" class="px-2 py-2 text-xs font-bold text-slate-600 bg-slate-100 dark:bg-slate-800 dark:text-slate-300 rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-all" wire:click="$set('amount', 500)">+₹500</button>
                    <button type="button" class="px-2 py-2 text-xs font-bold text-slate-600 bg-slate-100 dark:bg-slate-800 dark:text-slate-300 rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-all" wire:click="$set('amount', 1000)">+₹1000</button>
                    <button type="button" class="px-2 py-2 text-xs font-bold text-slate-600 bg-slate-100 dark:bg-slate-800 dark:text-slate-300 rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-all" wire:click="$set('amount', 5000)">+₹5000</button>
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

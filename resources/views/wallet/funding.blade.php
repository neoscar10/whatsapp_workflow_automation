<x-layouts.panel title="Fund Wallet - WhatsApp Cloud Panel" activeNav="wallet">
    <div class="py-10 px-6 max-w-4xl mx-auto">
        <div class="bg-white dark:bg-slate-800 shadow-xl rounded-2xl overflow-hidden border border-slate-100 dark:border-slate-700">
            <!-- Header Section with Gradient -->
            <div class="bg-gradient-to-r from-violet-600 via-indigo-600 to-blue-600 p-8 text-white relative">
                <div class="relative z-10">
                    <h1 class="text-3xl font-bold tracking-tight">Fund Your Wallet</h1>
                    <p class="mt-2 text-indigo-100 text-sm">Add funds instantly using Razorpay securely. Your transactions are safe and ledger-recorded.</p>
                </div>
                <div class="absolute inset-0 opacity-15 bg-[radial-gradient(circle_at_30%_20%,rgba(255,255,255,0.2),transparent_60%)]"></div>
            </div>

            <div class="p-8">
                <!-- Current Balance Display -->
                <div class="flex items-center justify-between p-4 mb-8 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Current Balance</p>
                        <p class="text-3xl font-extrabold text-slate-950 dark:text-white mt-1">
                            ₹{{ number_format(auth()->user()->wallet?->balance ?? 0, 2) }}
                        </p>
                    </div>
                    <span class="material-symbols-outlined text-4xl text-slate-400 dark:text-slate-600">account_balance_wallet</span>
                </div>

                <!-- Input Form -->
                <div id="funding-form-container">
                    <div class="space-y-6">
                        <div>
                            <label for="amount" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Amount (INR)</label>
                            <div class="mt-2 relative rounded-md shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                    <span class="text-slate-400 dark:text-slate-500 sm:text-sm">₹</span>
                                </div>
                                <input 
                                    type="number" 
                                    name="amount" 
                                    id="amount" 
                                    min="10" 
                                    value="500" 
                                    placeholder="500" 
                                    class="block w-full rounded-lg border-slate-300 dark:border-slate-700 pl-8 pr-12 focus:border-indigo-500 focus:ring-indigo-500 sm:text-lg font-semibold bg-white dark:bg-slate-900 text-slate-900 dark:text-white"
                                />
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4">
                                    <span class="text-slate-400 dark:text-slate-500 sm:text-sm">INR</span>
                                </div>
                            </div>
                            <p class="mt-2 text-xs text-slate-400 dark:text-slate-500">Minimum funding amount is ₹10.</p>
                            <div id="validation-error" class="hidden mt-2 text-sm text-red-600"></div>
                        </div>

                        <!-- Amount Quick Selectors -->
                        <div class="grid grid-cols-4 gap-3">
                            <button type="button" onclick="setAmount(100)" class="py-2.5 px-4 text-sm font-semibold rounded-lg bg-slate-50 hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800 transition duration-150">
                                +₹100
                            </button>
                            <button type="button" onclick="setAmount(500)" class="py-2.5 px-4 text-sm font-semibold rounded-lg bg-slate-50 hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800 transition duration-150">
                                +₹500
                            </button>
                            <button type="button" onclick="setAmount(1000)" class="py-2.5 px-4 text-sm font-semibold rounded-lg bg-slate-50 hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800 transition duration-150">
                                +₹1,000
                            </button>
                            <button type="button" onclick="setAmount(5000)" class="py-2.5 px-4 text-sm font-semibold rounded-lg bg-slate-50 hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800 transition duration-150">
                                +₹5,000
                            </button>
                        </div>

                        <!-- Proceed Button -->
                        <button 
                            type="button" 
                            id="pay-button" 
                            onclick="startFunding()" 
                            class="w-full flex items-center justify-center py-4 px-6 rounded-lg text-white font-semibold bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 shadow-md shadow-indigo-100 dark:shadow-none hover:shadow-lg transition duration-200 transform hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            <span>Proceed to Payment</span>
                        </button>
                    </div>
                </div>

                <!-- Loading State Screen -->
                <div id="loading-container" class="hidden flex-col items-center justify-center py-12">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 dark:border-indigo-400"></div>
                    <p class="mt-4 text-sm font-semibold text-slate-600 dark:text-slate-400" id="loading-text">Initializing payment...</p>
                </div>

                <!-- Success Screen -->
                <div id="success-container" class="hidden flex-col items-center justify-center py-12 text-center">
                    <span class="material-symbols-outlined text-6xl text-emerald-500 mb-4 animate-bounce">check_circle</span>
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Wallet Funded Successfully!</h2>
                    <p class="mt-2 text-slate-500 dark:text-slate-400" id="success-amount-msg"></p>
                    <a href="{{ url('/wallet') }}" class="mt-6 inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-slate-900 hover:bg-slate-800 dark:bg-indigo-600 dark:hover:bg-indigo-700 rounded-lg transition duration-150">
                        View Wallet Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <!-- Razorpay Checkout Script -->
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        function setAmount(val) {
            const amountInput = document.getElementById('amount');
            amountInput.value = val;
            document.getElementById('validation-error').classList.add('hidden');
        }

        async function startFunding() {
            const amountInput = document.getElementById('amount');
            const amount = parseFloat(amountInput.value);
            const errorDiv = document.getElementById('validation-error');
            const payButton = document.getElementById('pay-button');
            const formContainer = document.getElementById('funding-form-container');
            const loadingContainer = document.getElementById('loading-container');
            const loadingText = document.getElementById('loading-text');

            // Client side validation
            if (isNaN(amount) || amount < 10) {
                errorDiv.textContent = "Please enter a valid amount of ₹10 or more.";
                errorDiv.classList.remove('hidden');
                return;
            }

            errorDiv.classList.add('hidden');
            formContainer.classList.add('hidden');
            loadingContainer.classList.remove('hidden');
            loadingText.textContent = "Creating payment order...";

            try {
                // Initialize Payment on Server
                const response = await fetch('/api/v1/wallet/fund/initialize', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({ amount: amount, gateway: 'razorpay' })
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(result.message || "Failed to initialize payment.");
                }

                const transactionId = result.data.transaction_id;
                const checkoutData = result.data.checkout_data;

                // Configure Razorpay checkout popup options
                const options = {
                    key: checkoutData.key,
                    amount: checkoutData.amount,
                    currency: checkoutData.currency,
                    name: checkoutData.name,
                    description: checkoutData.description,
                    order_id: checkoutData.order_id,
                    prefill: checkoutData.prefill,
                    handler: async function (response) {
                        loadingContainer.classList.remove('hidden');
                        loadingText.textContent = "Verifying payment securely on server...";

                        try {
                            const verifyRes = await fetch(`/api/v1/wallet/fund/${transactionId}/verify`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                },
                                body: JSON.stringify({
                                    razorpay_payment_id: response.razorpay_payment_id,
                                    razorpay_order_id: response.razorpay_order_id,
                                    razorpay_signature: response.razorpay_signature
                                })
                            });

                            const verifyResult = await verifyRes.json();

                            if (!verifyRes.ok || !verifyResult.success) {
                                throw new Error(verifyResult.message || "Verification failed.");
                            }

                            // Show success screen
                            loadingContainer.classList.add('hidden');
                            document.getElementById('success-amount-msg').textContent = `₹${amount.toFixed(2)} has been credited to your wallet balance.`;
                            document.getElementById('success-container').classList.remove('hidden');

                        } catch (err) {
                            alert("Payment verification failed: " + err.message);
                            resetForm();
                        }
                    },
                    modal: {
                        ondismiss: function () {
                            alert("Payment checkout dismissed.");
                            resetForm();
                        }
                    },
                    theme: {
                        color: "#4f46e5"
                    }
                };

                const rzp = new Razorpay(options);
                rzp.open();

            } catch (err) {
                alert("Error initializing payment: " + err.message);
                resetForm();
            }
        }

        function resetForm() {
            document.getElementById('loading-container').classList.add('hidden');
            document.getElementById('funding-form-container').classList.remove('hidden');
        }
    </script>
    @endpush
</x-layouts.panel>

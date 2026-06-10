<div x-data @scroll-to-top.window="window.scrollTo({ top: 0, behavior: 'smooth' })" class="p-6 lg:p-8 w-full max-w-7xl mx-auto font-['Inter']">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h1 class="text-[24px] font-semibold tracking-tight text-slate-900 dark:text-white">Client Onboarding</h1>
            <p class="text-[14px] text-[#424656] dark:text-slate-400 mt-1">Configure client details, entity type, and required compliances.</p>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-800 border border-[#c2c6d8] dark:border-slate-700 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-8">
            <!-- Progress Stepper -->
            <div class="relative mb-12 max-w-2xl mx-auto">
                <div class="absolute top-1/2 left-0 w-full h-1 -translate-y-1/2 bg-[#f6f3f2] dark:bg-slate-900 rounded-full">
                    <div class="h-full bg-blue-600 dark:bg-blue-500 rounded-full transition-all duration-500" style="width: {{ ($step - 1) * 20 }}%;"></div>
                </div>
                
                <div class="relative flex justify-between">
                    @foreach([1 => 'Basic Info', 2 => 'Business Type', 3 => 'Intelligence', 4 => 'Assign Services', 5 => 'Documents', 6 => 'Review'] as $i => $label)
                        <div class="flex flex-col items-center">
                            <button type="button" class="w-10 h-10 rounded-full flex items-center justify-center font-semibold text-[14px] transition-all relative z-0 
                                {{ $step >= $i ? 'bg-blue-600 dark:bg-blue-500 text-white shadow-md' : 'bg-[#f6f3f2] dark:bg-slate-900 text-[#727687] dark:text-slate-500 border-2 border-white dark:border-slate-800' }}">
                                @if($step > $i)
                                    <span class="material-symbols-outlined text-[20px]">check</span>
                                @else
                                    {{ $i }}
                                @endif
                            </button>
                            <span class="absolute top-12 text-[12px] font-['Geist'] font-medium {{ $step >= $i ? 'text-[#1c1b1b] dark:text-white' : 'text-[#727687] dark:text-slate-500' }}">
                                {{ $label }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
            
            <div class="mt-8"></div>

            <form wire:submit.prevent="submit">
                @if($step === 1)
                    <!-- Step 1: Basic Info -->
                    <div class="max-w-3xl mx-auto animate-fade-in">
                        <div class="text-center mb-8">
                            <h2 class="text-[20px] font-semibold text-[#1c1b1b] dark:text-white">Client Information</h2>
                            <p class="text-[14px] text-[#424656] dark:text-slate-400 mt-2">Enter the basic details of the client to create their secure workspace.</p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-10">
                            <!-- Client Name -->
                            <div class="col-span-1">
                                <label class="block text-[13px] font-semibold text-[#1c1b1b] dark:text-slate-300 mb-2">Client Name <span class="text-[#ba1a1a] dark:text-red-400">*</span></label>
                                <div class="relative">
                                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[#727687] dark:text-slate-400 text-[20px] pointer-events-none">domain</span>
                                    <input type="text" wire:model="client_name" class="w-full pl-12 pr-4 py-3 bg-[#f6f3f2] dark:bg-slate-900 border border-transparent focus:bg-white dark:focus:bg-slate-800 focus:border-blue-600 dark:focus:border-blue-500 rounded-xl focus:ring-4 focus:ring-blue-600/10 dark:focus:ring-blue-500/10 text-[14px] text-[#1c1b1b] dark:text-white transition-all placeholder:text-[#727687] dark:placeholder:text-slate-500" placeholder="E.g., Acme Corp">
                                </div>
                                @error('client_name') <span class="text-[12px] text-[#ba1a1a] dark:text-red-400 mt-1.5 block">{{ $message }}</span> @enderror
                            </div>
                            
                            <!-- Email Address -->
                            <div class="col-span-1">
                                <label class="block text-[13px] font-semibold text-[#1c1b1b] dark:text-slate-300 mb-2">Email Address</label>
                                <div class="relative">
                                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[#727687] dark:text-slate-400 text-[20px] pointer-events-none">mail</span>
                                    <input type="email" wire:model="email" class="w-full pl-12 pr-4 py-3 bg-[#f6f3f2] dark:bg-slate-900 border border-transparent focus:bg-white dark:focus:bg-slate-800 focus:border-blue-600 dark:focus:border-blue-500 rounded-xl focus:ring-4 focus:ring-blue-600/10 dark:focus:ring-blue-500/10 text-[14px] text-[#1c1b1b] dark:text-white transition-all placeholder:text-[#727687] dark:placeholder:text-slate-500" placeholder="contact@acmecorp.com">
                                </div>
                                @error('email') <span class="text-[12px] text-[#ba1a1a] dark:text-red-400 mt-1.5 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Phone Number -->
                            <div class="col-span-1">
                                <label class="block text-[13px] font-semibold text-[#1c1b1b] dark:text-slate-300 mb-2">Phone Number <span class="text-[#ba1a1a] dark:text-red-400">*</span></label>
                                <div class="relative flex shadow-sm rounded-xl overflow-hidden focus-within:ring-4 focus-within:ring-blue-600/10 dark:focus-within:ring-blue-500/10 transition-all border border-transparent focus-within:border-blue-600 dark:focus-within:border-blue-500 bg-[#f6f3f2] dark:bg-slate-900 focus-within:bg-white dark:focus-within:bg-slate-800">
                                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#727687] dark:text-slate-400 text-[18px] pointer-events-none z-10">call</span>
                                    <select wire:model="country_code" class="w-[100px] shrink-0 pl-9 pr-6 py-3 bg-transparent border-none text-[13px] text-[#1c1b1b] dark:text-white appearance-none border-r border-r-[#c2c6d8]/50 dark:border-r-slate-700 focus:ring-0 cursor-pointer">
                                        <option value="+91" class="bg-white dark:bg-slate-800 text-[#1c1b1b] dark:text-white">🇮🇳 +91</option>
                                        <option value="+234" class="bg-white dark:bg-slate-800 text-[#1c1b1b] dark:text-white">🇳🇬 +234</option>
                                        <option value="+1" class="bg-white dark:bg-slate-800 text-[#1c1b1b] dark:text-white">🇺🇸 +1</option>
                                        <option value="+44" class="bg-white dark:bg-slate-800 text-[#1c1b1b] dark:text-white">🇬🇧 +44</option>
                                        <option value="+971" class="bg-white dark:bg-slate-800 text-[#1c1b1b] dark:text-white">🇦🇪 +971</option>
                                        <option value="+61" class="bg-white dark:bg-slate-800 text-[#1c1b1b] dark:text-white">🇦🇺 +61</option>
                                        <option value="+65" class="bg-white dark:bg-slate-800 text-[#1c1b1b] dark:text-white">🇸🇬 +65</option>
                                        <option value="+27" class="bg-white dark:bg-slate-800 text-[#1c1b1b] dark:text-white">🇿🇦 +27</option>
                                    </select>
                                    <div class="absolute left-[80px] top-1/2 -translate-y-1/2 pointer-events-none text-[#727687] dark:text-slate-400 z-10 flex items-center">
                                        <span class="material-symbols-outlined text-[16px]">expand_more</span>
                                    </div>
                                    <input type="tel" pattern="[0-9]*" oninput="this.value = this.value.replace(/[^0-9]/g, '')" wire:model="phone" class="w-full pl-4 pr-4 py-3 bg-transparent border-none text-[14px] text-[#1c1b1b] dark:text-white placeholder:text-[#727687] dark:placeholder:text-slate-500 focus:ring-0" placeholder="9876543210">
                                </div>
                                @error('phone') <span class="text-[12px] text-[#ba1a1a] dark:text-red-400 mt-1.5 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Address -->
                            <div class="col-span-1">
                                <label class="block text-[13px] font-semibold text-[#1c1b1b] dark:text-slate-300 mb-2">Address</label>
                                <div class="relative">
                                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[#727687] dark:text-slate-400 text-[20px] pointer-events-none">location_on</span>
                                    <input type="text" wire:model="address" class="w-full pl-12 pr-4 py-3 bg-[#f6f3f2] dark:bg-slate-900 border border-transparent focus:bg-white dark:focus:bg-slate-800 focus:border-blue-600 dark:focus:border-blue-500 rounded-xl focus:ring-4 focus:ring-blue-600/10 dark:focus:ring-blue-500/10 text-[14px] text-[#1c1b1b] dark:text-white transition-all placeholder:text-[#727687] dark:placeholder:text-slate-500" placeholder="123 Business Park">
                                </div>
                                @error('address') <span class="text-[12px] text-[#ba1a1a] dark:text-red-400 mt-1.5 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- City -->
                            <div class="col-span-1">
                                <label class="block text-[13px] font-semibold text-[#1c1b1b] dark:text-slate-300 mb-2">City</label>
                                <div class="relative">
                                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[#727687] dark:text-slate-400 text-[20px] pointer-events-none">location_city</span>
                                    <input type="text" wire:model="city" class="w-full pl-12 pr-4 py-3 bg-[#f6f3f2] dark:bg-slate-900 border border-transparent focus:bg-white dark:focus:bg-slate-800 focus:border-blue-600 dark:focus:border-blue-500 rounded-xl focus:ring-4 focus:ring-blue-600/10 dark:focus:ring-blue-500/10 text-[14px] text-[#1c1b1b] dark:text-white transition-all placeholder:text-[#727687] dark:placeholder:text-slate-500" placeholder="Mumbai">
                                </div>
                                @error('city') <span class="text-[12px] text-[#ba1a1a] dark:text-red-400 mt-1.5 block">{{ $message }}</span> @enderror
                            </div>
                            
                            <!-- State -->
                            <div class="col-span-1">
                                <label class="block text-[13px] font-semibold text-[#1c1b1b] dark:text-slate-300 mb-2">State</label>
                                <div class="relative">
                                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[#727687] dark:text-slate-400 text-[20px] pointer-events-none">map</span>
                                    <input type="text" wire:model="state" class="w-full pl-12 pr-4 py-3 bg-[#f6f3f2] dark:bg-slate-900 border border-transparent focus:bg-white dark:focus:bg-slate-800 focus:border-blue-600 dark:focus:border-blue-500 rounded-xl focus:ring-4 focus:ring-blue-600/10 dark:focus:ring-blue-500/10 text-[14px] text-[#1c1b1b] dark:text-white transition-all placeholder:text-[#727687] dark:placeholder:text-slate-500" placeholder="Maharashtra">
                                </div>
                                @error('state') <span class="text-[12px] text-[#ba1a1a] dark:text-red-400 mt-1.5 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Country -->
                            <div class="col-span-1">
                                <label class="block text-[13px] font-semibold text-[#1c1b1b] dark:text-slate-300 mb-2">Country</label>
                                <div class="relative">
                                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[#727687] dark:text-slate-400 text-[20px] pointer-events-none">public</span>
                                    <input type="text" wire:model="country" class="w-full pl-12 pr-4 py-3 bg-[#f6f3f2] dark:bg-slate-900 border border-transparent focus:bg-white dark:focus:bg-slate-800 focus:border-blue-600 dark:focus:border-blue-500 rounded-xl focus:ring-4 focus:ring-blue-600/10 dark:focus:ring-blue-500/10 text-[14px] text-[#1c1b1b] dark:text-white transition-all placeholder:text-[#727687] dark:placeholder:text-slate-500" placeholder="India">
                                </div>
                                @error('country') <span class="text-[12px] text-[#ba1a1a] dark:text-red-400 mt-1.5 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Empty space to balance the odd number of fields -->
                            <div class="col-span-1 hidden md:block"></div>

                            <!-- Notes -->
                            <div class="col-span-1 md:col-span-2">
                                <label class="block text-[13px] font-semibold text-[#1c1b1b] dark:text-slate-300 mb-2">Internal Notes</label>
                                <div class="relative">
                                    <span class="material-symbols-outlined absolute left-4 top-4 text-[#727687] dark:text-slate-400 text-[20px] pointer-events-none">notes</span>
                                    <textarea wire:model="notes" rows="4" class="w-full pl-12 pr-4 py-3 bg-[#f6f3f2] dark:bg-slate-900 border border-transparent focus:bg-white dark:focus:bg-slate-800 focus:border-blue-600 dark:focus:border-blue-500 rounded-xl focus:ring-4 focus:ring-blue-600/10 dark:focus:ring-blue-500/10 text-[14px] text-[#1c1b1b] dark:text-white transition-all placeholder:text-[#727687] dark:placeholder:text-slate-500 resize-none" placeholder="Any internal notes..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if($step === 2)
                    <!-- Step 2: Business Type -->
                    <div class="animate-fade-in">
                        @php
                            $businessDetails = [
                                'Hindu Undivided Family (HUF)' => [ 'icon' => 'family_restroom', 'title' => 'Hindu Undivided Family (HUF)', 'services' => ['PAN/TAN Registration', 'Tax Planning', 'ITR Filing'], 'docs' => ['HUF Deed', 'Karta ID', 'Bank Statement'], 'time' => '3-5 Business Days' ],
                                'Limited Liability Partnership (LLP)' => [ 'icon' => 'account_balance_wallet', 'title' => 'Limited Liability Partnership', 'services' => ['LLP Incorporation', 'RoC Compliance', 'Agreements'], 'docs' => ['LLP Agreement', 'DPIN Proofs', 'Office Address'], 'time' => '10-14 Business Days' ],
                                'One Person Company (OPC)' => [ 'icon' => 'person', 'title' => 'One Person Company', 'services' => ['OPC Registration', 'Director DIN', 'GST Registration'], 'docs' => ['ID Proof', 'Address Proof', 'Nominee Details'], 'time' => '7-10 Business Days' ],
                                'Partnership Firm' => [ 'icon' => 'groups', 'title' => 'Partnership Firm', 'services' => ['Partnership Deed', 'Firm Registration', 'Tax Compliance'], 'docs' => ['Deed Copy', 'Partners ID', 'Rent Agreement'], 'time' => '8-12 Business Days' ],
                                'Private Limited Company' => [ 'icon' => 'business', 'title' => 'Private Limited Company', 'services' => ['RoC Filing', 'Corporate Governance', 'Board Resolutions'], 'docs' => ['AoA / MoA', 'Director DIN', 'PAN/TAN'], 'time' => '7-10 Business Days' ],
                                'Proprietorship' => [ 'icon' => 'store', 'title' => 'Proprietorship', 'services' => ['GST Registration', 'Trade License', 'MSME Registration'], 'docs' => ['Address Proof', 'Shop Photo', 'PAN Card'], 'time' => '5-7 Business Days' ],
                                'Public Limited Company' => [ 'icon' => 'location_city', 'title' => 'Public Limited Company', 'services' => ['IPO Advisory', 'SEBI Compliance', 'Secretarial Audit'], 'docs' => ['AoA / MoA', 'Directors KYC', 'Prospectus'], 'time' => '15-30 Business Days' ],
                                'Section 8 Company' => [ 'icon' => 'volunteer_activism', 'title' => 'Section 8 Company', 'services' => ['Section 8 Registration', '80G & 12A Exemption', 'Annual Compliance'], 'docs' => ['Objective Proofs', 'Founders KYC', 'Utility Bill'], 'time' => '20-25 Business Days' ],
                                'Trust / Society / NGO' => [ 'icon' => 'volunteer_activism', 'title' => 'NGO / Trust / Society', 'services' => ['Trust Registration', 'FCRA Registration', 'Donation Planning'], 'docs' => ['Trust Deed', 'Bylaws', 'Donor Proofs'], 'time' => '15-20 Business Days' ],
                            ];

                            $selectedType = collect($businessTypes)->firstWhere('id', $business_type_id);
                            $selectedDetails = null;
                            if ($selectedType) {
                                $selectedDetails = $businessDetails[$selectedType->name] ?? [
                                    'icon' => 'domain', 'title' => $selectedType->name, 'services' => ['Standard Compliance', 'Tax Filing', 'Advisory'], 'docs' => ['ID Proof', 'Address Proof', 'Registration Cert'], 'time' => 'Varies'
                                ];
                            }
                        @endphp

                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                            <!-- Left Side: Header and Card Grid -->
                            <div class="lg:col-span-8">
                                <div class="mb-10 text-left">
                                    <h1 class="text-[24px] font-semibold text-[#1c1b1b] dark:text-white mb-3">Tell Us About Your Business</h1>
                                    <p class="text-[14px] text-[#424656] dark:text-slate-400 max-w-2xl">
                                        We’ll personalize your onboarding, document checklist, and compliance workflows based on your business structure.
                                    </p>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    @foreach($businessTypes as $type)
                                        @php
                                            $icon = $businessDetails[$type->name]['icon'] ?? 'domain';
                                        @endphp
                                        <button type="button" wire:key="btype-{{ $type->id }}" wire:click="$set('business_type_id', {{ $type->id }})" 
                                             class="group text-left p-6 rounded-xl border transition-all cursor-pointer relative 
                                             {{ $business_type_id == $type->id 
                                                ? 'border-blue-600 bg-[#f8faff] dark:bg-blue-900/40 dark:border-blue-500 shadow-md transform -translate-y-0.5' 
                                                : 'border-[#c2c6d8]/50 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-blue-600/50 dark:hover:border-blue-500/50 hover:shadow-sm' }}">
                                            
                                            <div class="mb-4 w-12 h-12 rounded-lg flex items-center justify-center transition-transform group-hover:scale-110 
                                                {{ $business_type_id == $type->id ? 'bg-blue-600 text-white dark:bg-blue-500' : 'bg-[#e1e0ff]/50 dark:bg-slate-700 text-blue-600 dark:text-blue-400' }}">
                                                <span class="material-symbols-outlined">{{ $icon }}</span>
                                            </div>
                                            <h3 class="text-[16px] font-semibold text-[#1c1b1b] dark:text-white mb-1">{{ $type->name }}</h3>
                                            <p class="text-[13px] text-[#424656] dark:text-slate-400">{{ $type->description }}</p>
                                            
                                            @if($business_type_id == $type->id)
                                                <div class="absolute top-4 right-4 text-blue-600 dark:text-blue-400">
                                                    <span class="material-symbols-outlined text-[24px]">check_circle</span>
                                                </div>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                                @error('business_type_id') <div class="text-[13px] text-[#ba1a1a] dark:text-red-400 mt-4 font-medium">{{ $message }}</div> @enderror
                            </div>

                            <!-- Right Sidebar: Contextual Preview -->
                            <aside class="hidden lg:block lg:col-span-4 h-fit sticky top-8">
                                <div class="bg-white/70 dark:bg-slate-800/70 backdrop-blur-md rounded-2xl p-8 flex flex-col gap-8 border border-[#c2c6d8]/50 dark:border-slate-700/50">
                                    @if(!$selectedDetails)
                                        <div class="py-12 text-center">
                                            <div class="w-16 h-16 bg-[#f6f3f2] dark:bg-slate-900 rounded-full flex items-center justify-center mx-auto mb-6 text-[#727687] dark:text-slate-500">
                                                <span class="material-symbols-outlined text-[32px]">info</span>
                                            </div>
                                            <h4 class="text-[18px] font-semibold text-[#1c1b1b] dark:text-white mb-2">Structure Details</h4>
                                            <p class="text-[14px] text-[#424656] dark:text-slate-400 px-4">Select a business type to see tailored requirements and timelines.</p>
                                        </div>
                                    @else
                                        <div class="animate-fade-in">
                                            <h4 class="text-[18px] font-semibold text-blue-600 dark:text-blue-400 mb-6">{{ $selectedDetails['title'] }}</h4>
                                            <div class="space-y-6">
                                                <section>
                                                    <h5 class="text-[12px] uppercase tracking-wider text-[#727687] dark:text-slate-400 font-semibold mb-3">Likely Services</h5>
                                                    <ul class="space-y-2">
                                                        @foreach($selectedDetails['services'] as $service)
                                                            <li class="flex items-center gap-2 text-[14px] text-[#1c1b1b] dark:text-white">
                                                                <span class="material-symbols-outlined text-[#006a61] dark:text-teal-400 text-[18px]">verified</span>
                                                                {{ $service }}
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </section>
                                                <section>
                                                    <h5 class="text-[12px] uppercase tracking-wider text-[#727687] dark:text-slate-400 font-semibold mb-3">Documents Required</h5>
                                                    <div class="flex flex-wrap gap-2">
                                                        @foreach($selectedDetails['docs'] as $doc)
                                                            <span class="px-3 py-1 bg-[#f0eded] dark:bg-slate-900 rounded-full text-[12px] text-[#424656] dark:text-slate-300 border border-[#c2c6d8]/30 dark:border-slate-700">
                                                                {{ $doc }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </section>
                                                <section class="p-4 bg-[#e1e0ff]/30 dark:bg-blue-900/20 rounded-xl border border-[#e1e0ff] dark:border-blue-900/50">
                                                    <div class="flex items-center gap-3">
                                                        <span class="material-symbols-outlined text-blue-600 dark:text-blue-400">schedule</span>
                                                        <div>
                                                            <p class="text-[12px] text-blue-600 dark:text-blue-400 font-medium">Estimated Setup Time</p>
                                                            <p class="text-[16px] font-semibold text-[#1c1b1b] dark:text-white">{{ $selectedDetails['time'] }}</p>
                                                        </div>
                                                    </div>
                                                </section>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                
                                @if($selectedDetails)
                                    <div class="mt-6 p-6 rounded-2xl bg-[#89f5e7]/10 dark:bg-teal-900/20 border border-[#89f5e7]/20 dark:border-teal-900/30 animate-fade-in">
                                        <p class="text-[14px] text-[#424656] dark:text-slate-300 italic">"CompliFlow helped us set up our {{ $selectedDetails['title'] }} entity quickly. Highly recommended."</p>
                                        <div class="mt-4 flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-[#89f5e7] dark:bg-teal-700 flex items-center justify-center text-[#00201d] dark:text-white font-bold text-xs">RK</div>
                                            <span class="text-[12px] font-medium text-[#1c1b1b] dark:text-white">Rahul K, Founder @ TechFlow</span>
                                        </div>
                                    </div>
                                @endif
                            </aside>
                        </div>
                    </div>
                @endif

                @if($step === 3)
                    <!-- Step 3: Intelligence Load -->
                    <div class="py-16 text-center animate-fade-in">
                        <div class="inline-block relative w-16 h-16 mb-6">
                            <span class="absolute inset-0 border-4 border-[#e1e0ff] dark:border-blue-900/50 rounded-full"></span>
                            <span class="absolute inset-0 border-4 border-blue-600 dark:border-blue-500 rounded-full border-t-transparent animate-spin"></span>
                        </div>
                        <h2 class="text-[24px] font-semibold text-[#1c1b1b] dark:text-white mb-2">AI Engine Analyzing...</h2>
                        <p class="text-[14px] text-[#424656] dark:text-slate-400 max-w-sm mx-auto">Gathering the specific compliance mapping and requirements for the selected entity type.</p>
                        <div class="hidden" wire:init="nextStep"></div>
                    </div>
                @endif

                @if($step === 4)
                    <!-- Step 4: Compliance Selection -->
                    <div class="animate-fade-in pb-32">
                        @if(session()->has('ai_success'))
                            <div class="p-4 mb-8 bg-blue-600/10 border border-blue-600/20 text-blue-800 rounded-xl text-[14px] font-medium flex items-center gap-3 shadow-sm">
                                <span class="material-symbols-outlined text-blue-600">auto_awesome</span>
                                {{ session('ai_success') }}
                            </div>
                        @endif
                        
                        @if($ai_error)
                            <div class="mb-8 p-6 rounded-2xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-900/50 flex flex-col items-center text-center animate-fade-in">
                                <div class="w-16 h-16 rounded-full bg-red-100 dark:bg-red-800/50 flex items-center justify-center mb-4">
                                    <span class="material-symbols-outlined text-red-600 dark:text-red-400 text-[32px]">warning</span>
                                </div>
                                <h3 class="text-xl font-bold text-red-800 dark:text-red-400 mb-2">AI Generation Failed</h3>
                                <p class="text-red-600 dark:text-red-300 mb-6 max-w-xl">{{ $ai_error }}</p>
                                <button type="button" wire:click="loadIntelligence" class="bg-red-600 text-white px-6 py-2.5 rounded-xl font-semibold hover:bg-red-700 transition-colors shadow-sm flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[18px]" wire:loading.class="animate-spin" wire:target="loadIntelligence">refresh</span>
                                    <span wire:loading.remove wire:target="loadIntelligence">Try Again</span>
                                    <span wire:loading wire:target="loadIntelligence">Retrying...</span>
                                </button>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                            <!-- Left Column: Service Configuration -->
                            <div class="lg:col-span-8">
                                <div class="mb-8">
                                    <span class="font-mono text-xs uppercase tracking-widest text-blue-600 dark:text-blue-400 font-semibold">Service Configuration</span>
                                    <h1 class="font-sans text-3xl text-[#1c1b1b] dark:text-white mt-2 font-bold">Assign Services & Compliances</h1>
                                    <div class="mt-6 bg-white/70 dark:bg-slate-800/80 backdrop-blur-md rounded-xl p-2 flex items-center gap-3 shadow-sm border border-[#c2c6d8]/30 dark:border-slate-700/50 focus-within:ring-2 focus-within:ring-blue-600/20 transition-all">
                                        <span class="material-symbols-outlined text-[#727687] dark:text-slate-400 ml-2">search</span>
                                        <input class="bg-transparent border-none focus:ring-0 w-full font-sans py-2 text-[#1c1b1b] dark:text-white dark:placeholder:text-slate-500" placeholder="Search for specific tax, registration or compliance services..." type="text"/>
                                        <button type="button" class="bg-[#f0eded] dark:bg-slate-700 text-[#1c1b1b] dark:text-white px-4 py-2 rounded-lg font-medium text-sm flex items-center gap-2 hover:bg-[#e5e2e1] dark:hover:bg-slate-600 transition-colors">
                                            <span class="material-symbols-outlined text-[16px]">tune</span>
                                            Filter
                                        </button>
                                    </div>
                                </div>

                                <!-- AI Recommendation Box -->
                                @php
                                    $businessTypeName = collect($businessTypes)->firstWhere('id', $business_type_id)?->name ?? 'Entity';
                                @endphp
                                <div class="mb-8 p-4 rounded-2xl bg-gradient-to-r from-blue-600/10 to-blue-500/10 dark:from-blue-900/30 dark:to-blue-800/30 border border-blue-600/20 dark:border-blue-500/20 flex gap-4 items-center">
                                    <div class="w-12 h-12 rounded-xl bg-white dark:bg-slate-800 shadow-sm flex items-center justify-center shrink-0">
                                        <span class="material-symbols-outlined text-[#4345d1] dark:text-blue-400" style="font-variation-settings: 'FILL' 1;">auto_awesome</span>
                                    </div>
                                    <div class="flex-grow">
                                        <h3 class="font-sans text-xl font-bold text-[#424656] dark:text-slate-200 text-[16px]">AI Smart Recommendation</h3>
                                        <p class="font-sans text-sm text-[#727687] dark:text-slate-400 mt-1">
                                            Based on your entity type as a <span class="text-blue-600 dark:text-blue-400 font-semibold">{{ $businessTypeName }}</span>, we have dynamically loaded the required compliances below.
                                        </p>
                                    </div>
                                    <button type="button" class="text-blue-600 font-semibold font-medium text-sm hover:underline shrink-0 px-2">Review Below</button>
                                </div>

                                <!-- Service Categories -->
                                <div class="space-y-4">
                                    @foreach($groupedCompliances as $index => $category)
                                        @php
                                            $icon = match($category->name) {
                                                'Registrations' => 'how_to_reg',
                                                'Tax Compliance' => 'receipt_long',
                                                'ROC & Corporate' => 'balance',
                                                default => 'verified',
                                            };
                                            $colorFrom = match($category->name) {
                                                'Registrations' => 'from-blue-400 to-blue-600',
                                                'Tax Compliance' => 'from-purple-400 to-purple-600',
                                                'ROC & Corporate' => 'from-teal-400 to-teal-600',
                                                default => 'from-gray-400 to-gray-600',
                                            };
                                        @endphp
                                        <div class="bg-white/70 dark:bg-slate-800/80 backdrop-blur-md rounded-2xl overflow-hidden border border-white dark:border-slate-700/50 shadow-sm" x-data="{ expanded: true }">
                                            <div @click="expanded = !expanded" class="p-6 flex justify-between items-center cursor-pointer hover:bg-white/50 dark:hover:bg-slate-800 transition-colors">
                                                <div class="flex items-center gap-4">
                                                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br {{ $colorFrom }} flex items-center justify-center text-white shadow-lg">
                                                        <span class="material-symbols-outlined">{{ $icon }}</span>
                                                    </div>
                                                    <div>
                                                        <h4 class="font-sans text-xl font-bold text-[#1c1b1b] dark:text-white">{{ $category->name }}</h4>
                                                        <p class="font-medium text-sm text-[#727687] dark:text-slate-400">{{ count($category->compliances) }} items available</p>
                                                    </div>
                                                </div>
                                                <span class="material-symbols-outlined text-[#727687] dark:text-slate-400 transition-transform duration-200" :class="expanded ? 'rotate-180' : ''">expand_more</span>
                                            </div>
                                            
                                            <div x-show="expanded" x-collapse class="px-6 pb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                                                @foreach($category->compliances as $compliance)
                                                    <label wire:key="comp-{{ $compliance->id }}" class="p-4 bg-white dark:bg-slate-900 rounded-xl transition-all flex flex-col group cursor-pointer border-2 
                                                        {{ in_array($compliance->id, $selectedCompliances) 
                                                            ? 'border-blue-600 ring-2 ring-blue-600/10 shadow-md dark:border-blue-500' 
                                                            : 'border-[#c2c6d8]/30 dark:border-slate-700 hover:border-blue-600/50 dark:hover:border-blue-500/50 hover:shadow-lg' }}">
                                                        
                                                        <div class="flex items-start justify-between gap-3 w-full">
                                                            <div class="flex gap-3 items-center flex-1 min-w-0 pr-2">
                                                                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 
                                                                    {{ in_array($compliance->id, $selectedCompliances) ? 'bg-blue-600/10 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400' : 'bg-[#f0eded] dark:bg-slate-800 text-[#727687] dark:text-slate-400' }}">
                                                                    <span class="material-symbols-outlined text-[20px]">{{ in_array($compliance->id, $selectedCompliances) ? 'verified' : 'corporate_fare' }}</span>
                                                                </div>
                                                                <h5 class="font-sans text-base font-bold text-[#1c1b1b] dark:text-white leading-tight break-words flex-1 min-w-0">{{ $compliance->name }}</h5>
                                                            </div>
                                                            
                                                            <div class="shrink-0 mt-0.5">
                                                                <input type="checkbox" class="sr-only" value="{{ $compliance->id }}" wire:model.live="selectedCompliances">
                                                                @if(in_array($compliance->id, $selectedCompliances))
                                                                    <span class="material-symbols-outlined text-blue-600" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                                                                @else
                                                                    <span class="material-symbols-outlined text-[#c2c6d8] group-hover:text-[#727687] transition-colors">add_circle</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        
                                                        <p class="font-mono text-xs text-[#727687] dark:text-slate-400 mt-3 leading-relaxed">{{ $compliance->description }}</p>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Right Column: Dynamic Sidebar -->
                            <aside class="lg:col-span-4 space-y-6">
                                <div class="bg-white/70 dark:bg-slate-800/80 backdrop-blur-md rounded-2xl p-6 border border-white dark:border-slate-700/50 shadow-xl sticky top-32">
                                    <div class="flex items-center justify-between mb-6">
                                        <h3 class="font-sans text-xl font-bold text-[#1c1b1b] dark:text-white">Workflow Preview</h3>
                                        <span class="bg-blue-600/10 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 px-2 py-1 rounded text-[10px] font-bold uppercase tracking-widest animate-pulse">Live</span>
                                    </div>
                                    <div class="space-y-8">
                                        <!-- Expected Documents -->
                                        <div>
                                            <div class="flex items-center gap-2 mb-4">
                                                <span class="material-symbols-outlined text-blue-600 dark:text-blue-400 text-[20px]">description</span>
                                                <h4 class="font-medium text-sm font-bold text-[#424656] dark:text-slate-300 uppercase tracking-wider">Expected Documents</h4>
                                                <span wire:loading wire:target="selectedCompliances" class="material-symbols-outlined animate-spin text-blue-500 text-[16px] ml-2">refresh</span>
                                            </div>
                                            
                                            <div class="space-y-5">
                                                @if(count($selectedCompliances) > 0)
                                                    @if($this->expectedDocuments->isEmpty())
                                                        <p class="text-xs text-[#727687] dark:text-slate-500 italic">No specific documents required for current selection.</p>
                                                    @else
                                                        @foreach($this->expectedDocuments as $groupName => $documents)
                                                            <div>
                                                                <h5 class="text-[11px] uppercase tracking-widest text-blue-600 dark:text-blue-400 font-bold mb-2">{{ $groupName }}</h5>
                                                                <div class="space-y-2">
                                                                    @foreach($documents as $doc)
                                                                        <div class="flex items-center justify-between p-3 bg-[#f0eded]/50 dark:bg-slate-900/50 rounded-lg">
                                                                            <div class="flex items-center gap-3">
                                                                                <div class="w-8 h-8 rounded-lg bg-white dark:bg-slate-800 flex items-center justify-center shadow-sm border border-[#c2c6d8]/30 dark:border-slate-700">
                                                                                    @if(in_array($doc->input_type, ['file', 'pdf', 'image', 'multi_file']))
                                                                                        <span class="material-symbols-outlined text-[16px] text-blue-600">file_upload</span>
                                                                                    @elseif(in_array($doc->input_type, ['text', 'textarea', 'date', 'number']))
                                                                                        <span class="material-symbols-outlined text-[16px] text-purple-600">keyboard</span>
                                                                                    @else
                                                                                        <span class="material-symbols-outlined text-[16px] text-[#727687]">post_add</span>
                                                                                    @endif
                                                                                </div>
                                                                                <div class="flex flex-col">
                                                                                    <span class="font-sans text-sm text-[#1c1b1b] dark:text-slate-300 font-medium">{{ $doc->name }}</span>
                                                                                    <span class="text-[10px] font-mono text-[#727687] dark:text-slate-500 uppercase">{{ in_array($doc->input_type, ['file', 'pdf', 'image', 'multi_file']) ? 'File Upload' : 'Data Input' }}</span>
                                                                                </div>
                                                                            </div>
                                                                            <span class="material-symbols-outlined text-teal-600 dark:text-teal-400 text-[18px]">check_circle</span>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                @else
                                                    <p class="text-xs text-[#727687] dark:text-slate-500 italic">Select services to generate document checklist.</p>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Onboarding Complexity -->
                                        <div>
                                            @php
                                                $complexity = count($selectedCompliances) > 5 ? 'High' : (count($selectedCompliances) > 2 ? 'Moderate' : 'Low');
                                                $complexityWidth = count($selectedCompliances) > 5 ? '85%' : (count($selectedCompliances) > 2 ? '50%' : '20%');
                                                $complexityColor = count($selectedCompliances) > 5 ? 'bg-red-500' : (count($selectedCompliances) > 2 ? 'bg-orange-500' : 'bg-green-500');
                                                $complexityTextColor = count($selectedCompliances) > 5 ? 'text-red-500' : (count($selectedCompliances) > 2 ? 'text-orange-500' : 'text-green-500');
                                            @endphp
                                            <div class="flex items-center justify-between mb-4">
                                                <div class="flex items-center gap-2">
                                                    <span class="material-symbols-outlined {{ $complexityTextColor }} text-[20px]">speed</span>
                                                    <h4 class="font-medium text-sm font-bold text-[#424656] dark:text-slate-300 uppercase tracking-wider">Onboarding Complexity</h4>
                                                </div>
                                                <span class="{{ $complexityTextColor }} font-bold text-sm">{{ count($selectedCompliances) == 0 ? 'N/A' : $complexity }}</span>
                                            </div>
                                            <div class="h-2 bg-[#f0eded] dark:bg-slate-700 rounded-full overflow-hidden">
                                                <div class="h-full {{ $complexityColor }} rounded-full transition-all duration-500" style="width: {{ count($selectedCompliances) == 0 ? '0%' : $complexityWidth }};"></div>
                                            </div>
                                        </div>

                                        <!-- Automation Preview -->
                                        <div class="p-4 rounded-xl bg-green-50/50 dark:bg-green-900/20 border border-green-100 dark:border-green-800/30">
                                            <div class="flex items-center gap-2 mb-2">
                                                <img alt="WhatsApp Icon" class="w-4 h-4" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDS2zT6ptwrj58Gtvw3i9WuQsm6yRfuGB88ZefbdkPuuwKDHagvYKbYkWiKeJfOZ2E497cE3YbwfbjDVkqSrLdteWz4rE1Wk8tK3t_k5UlwtpUmrSc0Pqqc-Qfbs2ZbOQHkbxGm2w9kZQWvJ0Y1aKFInClqqnJcn7iTWGuFj3jenQy4ba_D26oxP1hAxCrFfUff41KGYam9RmUtXqw_erPdPg6xBTkYn26x-Q9zDfhga3-VE7qXZWWDHsWr9cgdPezf1OHXuwt31Tk"/>
                                                <span class="font-medium font-bold text-green-800 dark:text-green-400 uppercase tracking-widest text-[10px]">WhatsApp Automation Enabled</span>
                                            </div>
                                            <p class="text-[12px] text-green-700 dark:text-green-500 leading-tight">Client will receive real-time filing reminders and document status updates directly via WhatsApp.</p>
                                        </div>

                                        <!-- Estimated Setup Time -->
                                        <div class="flex items-center justify-between border-t border-[#c2c6d8]/30 dark:border-slate-700/50 pt-6">
                                            <span class="font-sans text-sm text-[#727687] dark:text-slate-400">Estimated Setup Time</span>
                                            <div class="flex flex-col items-end">
                                                <span class="font-sans text-xl font-bold text-blue-600 dark:text-blue-400">{{ count($selectedCompliances) > 0 ? (count($selectedCompliances) > 3 ? '5-7 Days' : '3-5 Days') : '--' }}</span>
                                                <span class="text-[10px] text-[#727687] dark:text-slate-500 uppercase tracking-widest font-bold">Standard Track</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-white/70 dark:bg-slate-800/80 backdrop-blur-md p-4 rounded-2xl flex items-center gap-4 border border-white dark:border-slate-700/50 shadow-sm">
                                    <div class="w-10 h-10 rounded-full bg-[#f0eded] dark:bg-slate-700 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-[#727687] dark:text-slate-400">support_agent</span>
                                    </div>
                                    <div>
                                        <h5 class="text-[#1c1b1b] dark:text-white font-bold text-sm">Need assistance?</h5>
                                        <p class="text-[#727687] dark:text-slate-400 text-xs">Talk to a compliance expert now</p>
                                    </div>
                                </div>
                            </aside>
                        </div>
                    </div>

                    <!-- Bottom Bar -->
                    <div class="mt-10 pt-6 border-t border-[#c2c6d8]/50 dark:border-slate-700 flex flex-col md:flex-row items-center justify-between gap-4">
                        <button type="button" wire:click="$set('step', 2)" class="flex items-center gap-2 px-6 py-3 rounded-xl border border-[#c2c6d8]/50 dark:border-slate-700 hover:bg-[#fcf9f8] dark:hover:bg-slate-800 transition-colors font-semibold text-[#424656] dark:text-slate-300">
                            <span class="material-symbols-outlined">arrow_back</span>
                            Back
                        </button>
                        
                        <button type="button" wire:click="nextStep" class="w-full md:w-auto px-10 py-3.5 bg-blue-600 text-white font-bold rounded-xl shadow-lg shadow-blue-600/30 hover:shadow-blue-600/40 hover:-translate-y-0.5 active:scale-95 transition-all flex items-center justify-center gap-3 disabled:opacity-50 disabled:cursor-not-allowed" {{ count($selectedCompliances) == 0 ? 'disabled' : '' }}>
                            Continue to Documents
                            <span class="material-symbols-outlined">arrow_forward</span>
                        </button>
                    </div>
                @endif

                @if($step === 5)
                    <!-- Step 5: Document Collection -->
                    <div class="animate-fade-in pb-32">
                        @php
                            $requiredNowDocs = $this->expectedDocuments['Required Now'] ?? collect();
                            $collectedCount = collect($this->collectedData)->filter(fn($val) => !empty($val))->count();
                            $totalCount = $requiredNowDocs->count();
                            $progressPercent = $totalCount > 0 ? round(($collectedCount / $totalCount) * 100) : 100;
                        @endphp

                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                            <!-- Left/Center: Document Upload Section -->
                            <div class="lg:col-span-8 space-y-8">
                                <div>
                                    <h1 class="text-[32px] font-bold text-[#1c1b1b] dark:text-white mb-2">Collect Client Documents</h1>
                                    <p class="text-[16px] text-[#424656] dark:text-slate-400">Collect the initial documents required to proceed with onboarding this client.</p>
                                </div>

                                @if($totalCount === 0)
                                    <div class="bg-[#f0eded]/50 dark:bg-slate-900/50 p-8 rounded-2xl border border-[#c2c6d8]/50 dark:border-slate-700 text-center">
                                        <span class="material-symbols-outlined text-[#727687] dark:text-slate-500 text-[48px] mb-4">check_circle</span>
                                        <h3 class="text-xl font-bold text-[#1c1b1b] dark:text-white mb-2">No Initial Documents Required</h3>
                                        <p class="text-[#424656] dark:text-slate-400">You can proceed to complete the onboarding process.</p>
                                    </div>
                                @else
                                    <section>
                                        <h2 class="text-[20px] font-bold text-[#1c1b1b] dark:text-white mb-4 flex items-center gap-2">
                                            <span class="material-symbols-outlined text-blue-600 dark:text-blue-400">task</span>
                                            Required Information
                                        </h2>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            @foreach($requiredNowDocs as $doc)
                                                <div class="bg-white/70 dark:bg-slate-800/80 backdrop-blur-md p-5 rounded-2xl flex flex-col gap-4 border {{ !empty($collectedData[$doc->id]) ? 'border-green-500/50 dark:border-green-500/30' : 'border-[#c2c6d8]/50 dark:border-slate-700' }} hover:shadow-md transition-all">
                                                    <div class="flex items-start justify-between gap-3 w-full">
                                                        <div class="flex gap-3 items-center flex-1 min-w-0 pr-2">
                                                            <div class="w-8 h-8 rounded-lg shrink-0 {{ !empty($collectedData[$doc->id]) ? 'bg-green-500/10 text-green-600 dark:text-green-400' : 'bg-blue-600/10 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400' }} flex items-center justify-center">
                                                                @if(in_array($doc->input_type, ['file', 'pdf', 'image', 'multi_file']))
                                                                    <span class="material-symbols-outlined text-[20px]">upload_file</span>
                                                                @else
                                                                    <span class="material-symbols-outlined text-[20px]">keyboard</span>
                                                                @endif
                                                            </div>
                                                            <h3 class="font-sans text-base font-bold text-[#1c1b1b] dark:text-white leading-tight break-words flex-1 min-w-0">{{ $doc->name }}</h3>
                                                        </div>
                                                        <div class="shrink-0 mt-0.5">
                                                            @if(!empty($collectedData[$doc->id]))
                                                                <span class="material-symbols-outlined text-green-600" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                                                            @else
                                                                <span class="bg-[#f0eded] dark:bg-slate-700 text-[#727687] dark:text-slate-300 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-widest">Pending</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    
                                                    <p class="font-mono text-xs text-[#727687] dark:text-slate-400 leading-relaxed mt-1">{{ $doc->description ?? 'Required for compliance verification.' }}</p>

                                                    <div class="mt-2">
                                                        @if(in_array($doc->input_type, ['file', 'pdf', 'image', 'multi_file']))
                                                            <div class="relative">
                                                                <input type="file" wire:model.live="collectedData.{{ $doc->id }}" id="doc_{{ $doc->id }}" class="hidden" />
                                                                <label for="doc_{{ $doc->id }}" class="flex items-center justify-center w-full px-4 py-3 border-2 border-dashed {{ !empty($collectedData[$doc->id]) ? 'border-green-500/50 bg-green-500/5' : 'border-[#c2c6d8] dark:border-slate-600 bg-[#f6f3f2] dark:bg-slate-900/50 hover:bg-[#f0eded] dark:hover:bg-slate-800' }} rounded-xl cursor-pointer transition-colors group">
                                                                    @if(!empty($collectedData[$doc->id]))
                                                                        <div class="flex items-center gap-2 text-green-700 dark:text-green-400 font-medium text-sm">
                                                                            <span class="material-symbols-outlined">description</span>
                                                                            File Selected
                                                                        </div>
                                                                    @else
                                                                        <div class="flex items-center gap-2 text-blue-600 dark:text-blue-400 font-medium text-sm group-hover:scale-105 transition-transform">
                                                                            <span class="material-symbols-outlined text-[18px]">upload</span>
                                                                            Click to Upload
                                                                        </div>
                                                                    @endif
                                                                </label>
                                                                <div wire:loading.flex wire:target="collectedData.{{ $doc->id }}" class="absolute inset-0 bg-white/80 dark:bg-slate-800/80 rounded-xl items-center justify-center">
                                                                    <span class="material-symbols-outlined animate-spin text-blue-600">refresh</span>
                                                                </div>
                                                            </div>
                                                        @else
                                                            <input type="{{ $doc->input_type === 'date' ? 'date' : ($doc->input_type === 'number' ? 'number' : 'text') }}" 
                                                                wire:model.lazy="collectedData.{{ $doc->id }}" 
                                                                class="w-full px-4 py-3 bg-[#f6f3f2] dark:bg-slate-900 border {{ !empty($collectedData[$doc->id]) ? 'border-green-500/50' : 'border-[#c2c6d8]/50 dark:border-slate-700' }} focus:bg-white dark:focus:bg-slate-800 focus:border-blue-600 dark:focus:border-blue-500 rounded-xl text-sm text-[#1c1b1b] dark:text-white transition-all placeholder:text-[#727687] dark:placeholder:text-slate-500" 
                                                                placeholder="Enter details here...">
                                                        @endif
                                                        @error('collectedData.'.$doc->id) <span class="text-[12px] text-red-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </section>
                                @endif
                            </div>

                            <!-- Right Sidebar: Readiness -->
                            <aside class="hidden lg:block lg:col-span-4 space-y-6">
                                <div class="bg-white/70 dark:bg-slate-800/80 backdrop-blur-md rounded-2xl p-8 border border-white dark:border-slate-700/50 shadow-xl sticky top-32">
                                    <h3 class="font-bold text-xl text-[#1c1b1b] dark:text-white mb-8">Collection Progress</h3>
                                    
                                    <!-- Circular Progress -->
                                    <div class="flex flex-col items-center pb-8 border-b border-[#c2c6d8]/30 dark:border-slate-700/50">
                                        <div class="relative w-32 h-32 flex items-center justify-center mb-4">
                                            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                                                <path class="text-[#f0eded] dark:text-slate-700" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="3.8"/>
                                                <path class="text-blue-600 dark:text-blue-500 transition-all duration-1000 ease-out" stroke-dasharray="{{ $progressPercent }}, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="3.8"/>
                                            </svg>
                                            <div class="absolute inset-0 flex items-center justify-center flex-col">
                                                <span class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $progressPercent }}<span class="text-lg">%</span></span>
                                            </div>
                                        </div>
                                        <p class="font-bold text-[#1c1b1b] dark:text-white text-lg">Readiness Score</p>
                                        <p class="text-sm text-[#727687] dark:text-slate-400 text-center mt-2 px-4">Complete remaining documents to hit 100% and enable onboarding completion.</p>
                                    </div>

                                    <div class="pt-6 flex justify-between items-center gap-2">
                                        <span class="text-sm font-semibold text-[#424656] dark:text-slate-300 whitespace-nowrap">Documents Collected</span>
                                        <span class="bg-[#f0eded] dark:bg-slate-700 text-[#1c1b1b] dark:text-white px-3 py-1 rounded-lg text-sm font-bold whitespace-nowrap">{{ $collectedCount }} / {{ $totalCount }}</span>
                                    </div>
                                </div>
                            </aside>
                        </div>

                        <!-- Bottom Bar -->
                        <div class="mt-12 pt-6 border-t border-[#c2c6d8]/50 dark:border-slate-700 flex flex-col md:flex-row items-center justify-between gap-4">
                            <button type="button" wire:click="$set('step', 4)" class="flex items-center gap-2 px-6 py-3 rounded-xl border border-[#c2c6d8]/50 dark:border-slate-700 hover:bg-[#fcf9f8] dark:hover:bg-slate-800 transition-colors font-semibold text-[#424656] dark:text-slate-300">
                                <span class="material-symbols-outlined">arrow_back</span>
                                Back to Setup
                            </button>
                            
                            <button type="button" wire:click="nextStep" class="w-full md:w-auto px-10 py-3.5 bg-blue-600 text-white font-bold rounded-xl shadow-lg shadow-blue-600/30 hover:shadow-blue-600/40 hover:-translate-y-0.5 active:scale-95 transition-all flex items-center justify-center gap-3 disabled:opacity-50 disabled:cursor-not-allowed" {{ $collectedCount < $totalCount ? 'disabled' : '' }}>
                                Continue to Review
                                <span class="material-symbols-outlined">arrow_forward</span>
                            </button>
                        </div>
                    </div>
                @endif


                @if($step === 6)
                    <!-- Step 6: Review & Verification -->
                    <div class="animate-fade-in pb-32">
                        <section class="mb-12">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h1 class="text-[32px] font-bold text-[#1c1b1b] dark:text-white mb-2 tracking-tight">Review & Verification</h1>
                                    <p class="text-[16px] text-[#424656] dark:text-slate-400">Final audit before activating your automated compliance engine.</p>
                                </div>
                            </div>
                        </section>

                        <div class="flex flex-col gap-8">
                            <!-- Business Profile Summary -->
                            <div class="bg-white/70 dark:bg-slate-800/80 backdrop-blur-md rounded-xl p-8 border border-[#c2c6d8]/50 dark:border-slate-700 shadow-sm">
                                <div class="flex justify-between items-center mb-6">
                                    <div class="flex items-center gap-2 text-blue-600 dark:text-blue-400">
                                        <span class="material-symbols-outlined">domain</span>
                                        <h2 class="text-xl font-bold text-[#1c1b1b] dark:text-white">Business Profile Summary</h2>
                                    </div>
                                    <button wire:click="$set('step', 1)" class="text-blue-600 dark:text-blue-400 font-bold text-sm hover:bg-blue-600/10 dark:hover:bg-blue-400/10 px-4 py-2 rounded-lg transition-all">EDIT</button>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div>
                                        <p class="text-[#727687] dark:text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Legal Name</p>
                                        <p class="text-base text-[#1c1b1b] dark:text-white font-semibold">{{ $client_name }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[#727687] dark:text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Contact Info</p>
                                        <p class="text-base text-[#1c1b1b] dark:text-white font-semibold">{{ $country_code }} {{ $phone }} <br> <span class="text-sm font-normal">{{ $email }}</span></p>
                                    </div>
                                    <div class="md:col-span-2">
                                        <p class="text-[#727687] dark:text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Registered Address</p>
                                        <p class="text-base text-[#1c1b1b] dark:text-white font-semibold">{{ $address }}{{ $city ? ', '.$city : '' }}{{ $state ? ', '.$state : '' }}{{ $country ? ', '.$country : '' }}</p>
                                    </div>
                                    <div class="md:col-span-2">
                                        <p class="text-[#727687] dark:text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Entity Type</p>
                                        <p class="text-base text-[#1c1b1b] dark:text-white font-semibold">{{ collect($businessTypes)->firstWhere('id', $business_type_id)?->name ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Selected Services -->
                            <div class="bg-white/70 dark:bg-slate-800/80 backdrop-blur-md rounded-xl p-8 border border-[#c2c6d8]/50 dark:border-slate-700 shadow-sm">
                                <div class="flex justify-between items-center mb-6">
                                    <div class="flex items-center gap-2 text-blue-600 dark:text-blue-400">
                                        <span class="material-symbols-outlined">layers</span>
                                        <h2 class="text-xl font-bold text-[#1c1b1b] dark:text-white">Selected Services</h2>
                                    </div>
                                    <button wire:click="setStep(4)" class="text-blue-600 dark:text-blue-400 font-bold text-sm hover:bg-blue-600/10 dark:hover:bg-blue-400/10 px-4 py-2 rounded-lg transition-all">EDIT</button>
                                </div>
                                <div class="flex flex-col gap-3">
                                    @foreach(\Modules\CA\Models\CACompliance::whereIn('id', $selectedCompliances)->get() as $compliance)
                                        <div class="flex items-center justify-between p-4 border border-[#c2c6d8]/30 dark:border-slate-700/50 rounded-lg hover:border-blue-600/30 transition-all">
                                            <div class="flex items-center gap-4">
                                                <div class="w-10 h-10 rounded-lg bg-blue-600/10 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                                                    <span class="material-symbols-outlined">{{ $compliance->is_recurring ? 'event_repeat' : 'task' }}</span>
                                                </div>
                                                <div>
                                                    <p class="text-base font-bold text-[#1c1b1b] dark:text-white">{{ $compliance->name }}</p>
                                                    <p class="text-xs text-[#727687] dark:text-slate-400 line-clamp-1">{{ $compliance->description }}</p>
                                                </div>
                                            </div>
                                            <div class="hidden sm:flex items-center gap-4 shrink-0">
                                                <span class="bg-teal-500/10 text-teal-700 dark:text-teal-400 px-3 py-1 rounded-full text-xs font-bold whitespace-nowrap">Automation Enabled</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Document Verification -->
                            @if(count($this->expectedDocuments['Required Now'] ?? collect()) > 0)
                                <div class="bg-white/70 dark:bg-slate-800/80 backdrop-blur-md rounded-xl p-8 border border-[#c2c6d8]/50 dark:border-slate-700 shadow-sm">
                                    <div class="flex justify-between items-center mb-6">
                                        <div class="flex items-center gap-2 text-blue-600 dark:text-blue-400">
                                            <span class="material-symbols-outlined">verified_user</span>
                                            <h2 class="text-xl font-bold text-[#1c1b1b] dark:text-white">Document Verification</h2>
                                        </div>
                                        <button wire:click="setStep(5)" class="text-blue-600 dark:text-blue-400 font-bold text-sm hover:bg-blue-600/10 dark:hover:bg-blue-400/10 px-4 py-2 rounded-lg transition-all">EDIT</button>
                                    </div>
                                    <div class="space-y-3">
                                        @foreach($this->expectedDocuments['Required Now'] as $doc)
                                            <div class="flex items-center justify-between p-4 bg-[#f6f3f2]/50 dark:bg-slate-900/50 rounded-lg border border-transparent">
                                                <div class="flex items-center gap-4">
                                                    <span class="material-symbols-outlined text-green-600 dark:text-green-500">verified</span>
                                                    <span class="text-base font-medium text-[#1c1b1b] dark:text-white">{{ $doc->name }}</span>
                                                </div>
                                                <span class="text-green-600 dark:text-green-500 font-bold text-xs">Verified ✓</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Sticky Footer Bottom Bar -->
                        <div class="mt-12 pt-6 border-t border-[#c2c6d8]/50 dark:border-slate-700 flex flex-col md:flex-row items-center justify-between gap-4">
                            <button type="button" wire:click="setStep(5)" class="flex items-center gap-2 px-6 py-3 rounded-xl border border-[#c2c6d8]/50 dark:border-slate-700 hover:bg-[#fcf9f8] dark:hover:bg-slate-800 transition-colors font-semibold text-[#424656] dark:text-slate-300">
                                <span class="material-symbols-outlined">arrow_back</span>
                                Back to Documents
                            </button>
                            
                            <button type="button" wire:click="submit" class="w-full md:w-auto px-10 py-3.5 bg-blue-600 text-white font-bold rounded-xl shadow-lg shadow-blue-600/30 hover:shadow-blue-600/40 hover:-translate-y-0.5 active:scale-95 transition-all flex items-center justify-center gap-3">
                                Activate Compliance Setup
                                <span class="material-symbols-outlined">rocket_launch</span>
                            </button>
                        </div>
                    </div>
                @endif


                <!-- Navigation Bar for steps 1-3 -->
                @if($step < 4)
                    <div class="mt-10 pt-6 border-t border-[#c2c6d8]/50 dark:border-slate-700 flex items-center justify-between">
                        @if($step > 1)
                            <button type="button" wire:click="previousStep" class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#f6f3f2] hover:bg-[#e5e2e1] dark:bg-slate-900 dark:hover:bg-slate-700 text-[#1c1b1b] dark:text-white rounded-lg font-medium text-[14px] transition-all">
                                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                                Back
                            </button>
                        @else
                            <div></div>
                        @endif
                        
                        <div class="flex items-center gap-4">
                            @if($step < 3)
                                <button type="button" wire:click="nextStep" wire:loading.attr="disabled" class="inline-flex items-center gap-2 px-8 py-2.5 bg-blue-600 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-500 text-white rounded-lg font-medium text-[14px] transition-all shadow-md disabled:opacity-75 disabled:cursor-wait">
                                    <span wire:loading.remove wire:target="nextStep">Continue</span>
                                    <span wire:loading wire:target="nextStep">Analyzing...</span>
                                    <span wire:loading.remove wire:target="nextStep" class="material-symbols-outlined text-[18px]">arrow_forward</span>
                                    <span wire:loading wire:target="nextStep" class="material-symbols-outlined text-[18px] animate-spin">sync</span>
                                </button>
                            @endif
                        </div>
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>



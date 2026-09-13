<div class="mx-auto w-full max-w-5xl p-6 md:p-8 space-y-6 flex-1 overflow-y-auto no-scrollbar">
    <!-- Header -->
    <div>
        <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Business Verification</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
            Tell us about your business and provide the official documents that apply to your entity. We’ll review the information before your business is submitted for WhatsApp onboarding.
        </p>
    </div>

    <!-- Session Flash Message -->
    @if (session()->has('success'))
        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900/50 text-emerald-800 dark:text-emerald-300 text-xs font-bold flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-base">check_circle</span>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-900 dark:hover:text-emerald-200">✕</button>
        </div>
    @endif

    <!-- HORIZONTAL STEPS TAB NAVIGATION -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <!-- Step 1 Tab -->
            <button 
                type="button" 
                wire:click="goStep(1)"
                class="flex items-center gap-3 p-3 rounded-xl border text-left transition-all {{ $currentStep === 1 ? 'border-primary bg-primary/10 dark:bg-primary/20 ring-2 ring-primary/20' : ($currentStep > 1 ? 'border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40' : 'border-slate-200 dark:border-slate-800 opacity-60') }}"
            >
                <div class="flex size-8 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $currentStep === 1 ? 'bg-primary text-white' : ($currentStep > 1 ? 'bg-primary/20 text-primary font-black' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400') }}">
                    {{ $currentStep > 1 ? '✓' : '1' }}
                </div>
                <div class="min-w-0">
                    <div class="text-xs font-bold text-slate-900 dark:text-white truncate">Business type</div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate">Choose entity</div>
                </div>
            </button>

            <!-- Step 2 Tab -->
            <button 
                type="button" 
                wire:click="goStep(2)"
                class="flex items-center gap-3 p-3 rounded-xl border text-left transition-all {{ $currentStep === 2 ? 'border-primary bg-primary/10 dark:bg-primary/20 ring-2 ring-primary/20' : ($currentStep > 2 ? 'border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40' : 'border-slate-200 dark:border-slate-800 opacity-60') }}"
            >
                <div class="flex size-8 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $currentStep === 2 ? 'bg-primary text-white' : ($currentStep > 2 ? 'bg-primary/20 text-primary font-black' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400') }}">
                    {{ $currentStep > 2 ? '✓' : '2' }}
                </div>
                <div class="min-w-0">
                    <div class="text-xs font-bold text-slate-900 dark:text-white truncate">Business details</div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate">Tell us about it</div>
                </div>
            </button>

            <!-- Step 3 Tab -->
            <button 
                type="button" 
                wire:click="goStep(3)"
                class="flex items-center gap-3 p-3 rounded-xl border text-left transition-all {{ $currentStep === 3 ? 'border-primary bg-primary/10 dark:bg-primary/20 ring-2 ring-primary/20' : ($currentStep > 3 ? 'border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40' : 'border-slate-200 dark:border-slate-800 opacity-60') }}"
            >
                <div class="flex size-8 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $currentStep === 3 ? 'bg-primary text-white' : ($currentStep > 3 ? 'bg-primary/20 text-primary font-black' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400') }}">
                    {{ $currentStep > 3 ? '✓' : '3' }}
                </div>
                <div class="min-w-0">
                    <div class="text-xs font-bold text-slate-900 dark:text-white truncate">Documents</div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate">Upload evidence</div>
                </div>
            </button>

            <!-- Step 4 Tab -->
            <button 
                type="button" 
                wire:click="goStep(4)"
                class="flex items-center gap-3 p-3 rounded-xl border text-left transition-all {{ $currentStep === 4 ? 'border-primary bg-primary/10 dark:bg-primary/20 ring-2 ring-primary/20' : ($currentStep > 4 ? 'border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40' : 'border-slate-200 dark:border-slate-800 opacity-60') }}"
            >
                <div class="flex size-8 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $currentStep === 4 ? 'bg-primary text-white' : ($currentStep > 4 ? 'bg-primary/20 text-primary font-black' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400') }}">
                    {{ $currentStep > 4 ? '✓' : '4' }}
                </div>
                <div class="min-w-0">
                    <div class="text-xs font-bold text-slate-900 dark:text-white truncate">Review & submit</div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate">Check and send</div>
                </div>
            </button>
        </div>
    </div>

    <!-- MAIN STEP WORKSPACE CARD -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 md:p-8 shadow-sm">
        <!-- STEP 1: BUSINESS TYPE SELECTION -->
        @if($currentStep === 1)
            <div class="space-y-6">
                <div class="flex justify-between items-start gap-4">
                    <div>
                        <div class="text-[11px] uppercase tracking-widest font-extrabold text-primary mb-1">Step 1 of 4</div>
                        <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">What type of business are you?</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Your selection determines the business evidence we’ll ask you for next.</p>
                    </div>
                    <span class="text-xs font-bold text-primary bg-primary/10 dark:bg-primary/20 px-3 py-1 rounded-full whitespace-nowrap">Required</span>
                </div>

                @error('business_type')
                    <div class="p-4 text-xs font-bold text-rose-700 bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900/50 rounded-xl">{{ $message }}</div>
                @enderror

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @php
                        $entities = [
                            'Sole proprietorship' => ['code' => 'SP', 'desc' => 'A business owned and operated by one individual.'],
                            'Partnership' => ['code' => 'PF', 'desc' => 'A business operated by two or more partners.'],
                            'LLP' => ['code' => 'LL', 'desc' => 'A registered LLP with separate legal identity.'],
                            'Private / public company' => ['code' => 'CO', 'desc' => 'A company registered as a corporate entity.'],
                            'Trust / society / NGO' => ['code' => 'NG', 'desc' => 'A registered non-profit or other eligible organization.'],
                        ];
                    @endphp

                    @foreach($entities as $name => $info)
                        <button 
                            type="button" 
                            wire:click="selectEntityType('{{ $name }}')" 
                            class="p-5 rounded-2xl border text-left transition-all cursor-pointer {{ $business_type === $name ? 'border-primary bg-primary/5 dark:bg-primary/10 ring-2 ring-primary/20 shadow-sm' : 'border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20 hover:border-slate-300 dark:hover:border-slate-700 hover:bg-slate-100/50 dark:hover:bg-slate-800/40' }}"
                        >
                            <div class="size-10 rounded-xl bg-primary/10 text-primary dark:bg-primary/20 dark:text-primary font-extrabold flex items-center justify-center mb-3 text-xs">
                                {{ $info['code'] }}
                            </div>
                            <strong class="block text-sm font-bold text-slate-900 dark:text-white mb-1">{{ $name }}</strong>
                            <span class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed block">{{ $info['desc'] }}</span>
                        </button>
                    @endforeach
                </div>

                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                    <strong class="text-slate-900 dark:text-white">Why we ask:</strong> Different business structures have different official records. We’ll show document options relevant to the business type you select.
                </div>

                <div class="flex justify-end pt-4 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" wire:click="goStep(2)" {{ !$business_type ? 'disabled' : '' }} class="px-6 py-2.5 rounded-xl bg-primary hover:bg-primary/90 text-white font-bold text-xs transition-all disabled:opacity-40 disabled:cursor-not-allowed shadow-md shadow-primary/20">
                        Continue
                    </button>
                </div>
            </div>
        @endif

        <!-- STEP 2: BUSINESS DETAILS FORM -->
        @if($currentStep === 2)
            <div class="space-y-6">
                <div class="flex justify-between items-start gap-4">
                    <div>
                        <div class="text-[11px] uppercase tracking-widest font-extrabold text-primary mb-1">Step 2 of 4</div>
                        <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Tell us about your business</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Use your legal business information where requested. Your display name can be customer-facing.</p>
                    </div>
                    <span class="text-xs font-bold text-primary bg-primary/10 dark:bg-primary/20 px-3 py-1 rounded-full whitespace-nowrap">{{ $business_type ?: 'Business Details' }}</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-900 dark:text-white mb-2">Legal business name <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="legal_name" placeholder="Exactly as shown on official records" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-3 text-xs font-medium text-slate-900 dark:text-white outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all" />
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 block">This should match the name on your supporting business document.</span>
                        @error('legal_name') <span class="text-xs text-rose-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-900 dark:text-white mb-2">WhatsApp display name <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="display_name" placeholder="Name customers will see" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-3 text-xs font-medium text-slate-900 dark:text-white outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all" />
                        @error('display_name') <span class="text-xs text-rose-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-900 dark:text-white mb-2">Business category / industry <span class="text-rose-500">*</span></label>
                        <select wire:model="category" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-3 text-xs font-medium text-slate-900 dark:text-white outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all">
                            <option value="">Select a category</option>
                            <option value="Retail">Retail</option>
                            <option value="Technology">Technology</option>
                            <option value="Professional services">Professional services</option>
                            <option value="Education">Education</option>
                            <option value="Food & hospitality">Food & hospitality</option>
                            <option value="Health & wellness">Health & wellness</option>
                            <option value="Other">Other</option>
                        </select>
                        @error('category') <span class="text-xs text-rose-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-900 dark:text-white mb-2">Business website</label>
                        <input type="url" wire:model="website" placeholder="https://example.com" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-3 text-xs font-medium text-slate-900 dark:text-white outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all" />
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 block">If you don’t have a website, you can leave this blank.</span>
                        @error('website') <span class="text-xs text-rose-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-900 dark:text-white mb-2">Registered business address <span class="text-rose-500">*</span></label>
                        <textarea wire:model="address" rows="3" placeholder="Enter the official registered or business address" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-3 text-xs font-medium text-slate-900 dark:text-white outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all resize-y min-h-[80px]"></textarea>
                        @error('address') <span class="text-xs text-rose-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-900 dark:text-white mb-2">Authorized signatory <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="signatory_name" placeholder="Full name" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-3 text-xs font-medium text-slate-900 dark:text-white outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all" />
                        @error('signatory_name') <span class="text-xs text-rose-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-900 dark:text-white mb-2">Designation / role <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="signatory_designation" placeholder="e.g. Director, Owner, Partner" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-3 text-xs font-medium text-slate-900 dark:text-white outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all" />
                        @error('signatory_designation') <span class="text-xs text-rose-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-900 dark:text-white mb-2">Business email <span class="text-rose-500">*</span></label>
                        <input type="email" wire:model="business_email" placeholder="name@business.com" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-3 text-xs font-medium text-slate-900 dark:text-white outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all" />
                        @error('business_email') <span class="text-xs text-rose-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-900 dark:text-white mb-2">Business phone <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="business_phone" placeholder="+234 ..." class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-3 text-xs font-medium text-slate-900 dark:text-white outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all" />
                        @error('business_phone') <span class="text-xs text-rose-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-900 dark:text-white mb-2">WhatsApp phone number to register <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="wa_phone" placeholder="+234 ..." class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-4 py-3 text-xs font-medium text-slate-900 dark:text-white outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all" />
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 block">This is the number you intend to connect to the WhatsApp Business Platform.</span>
                        @error('wa_phone') <span class="text-xs text-rose-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/50 text-xs text-amber-800 dark:text-amber-300 leading-relaxed">
                    <strong>Before you continue:</strong> If this number is already active on WhatsApp or WhatsApp Business, additional eligibility or migration steps may apply.
                </div>

                <div class="flex justify-between items-center pt-4 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" wire:click="goStep(1)" class="px-5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                        Back
                    </button>
                    <button type="button" wire:click="goStep(3)" class="px-6 py-2.5 rounded-xl bg-primary hover:bg-primary/90 text-white font-bold text-xs transition-all shadow-md shadow-primary/20">
                        Continue
                    </button>
                </div>
            </div>
        @endif

        <!-- STEP 3: DOCUMENT UPLOADS -->
        @if($currentStep === 3)
            <div class="space-y-6">
                <div class="flex justify-between items-start gap-4">
                    <div>
                        <div class="text-[11px] uppercase tracking-widest font-extrabold text-primary mb-1">Step 3 of 4</div>
                        <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Provide your business documents</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Upload clear, valid documents that support the business information you entered. The options below change based on your business type.</p>
                    </div>
                    <span class="text-xs font-bold text-primary bg-primary/10 dark:bg-primary/20 px-3 py-1 rounded-full whitespace-nowrap">{{ $business_type }}</span>
                </div>

                <div class="p-4 rounded-xl bg-primary/10 dark:bg-primary/20 border border-primary/20 text-xs text-slate-800 dark:text-slate-200 leading-relaxed">
                    <strong class="text-primary">Keep it simple:</strong> Choose the official document that best proves the requested information. We may ask for another document during review if the evidence is incomplete or unclear.
                </div>

                <div class="text-[11px] text-slate-500 dark:text-slate-400">Accepted files: PDF, JPG or PNG · Maximum 10 MB per file · Documents should be readable and in English.</div>

                <!-- Dynamic Document List -->
                <div class="space-y-4">
                    @forelse($verification->documents as $index => $doc)
                        @php
                            $latest = $doc->latestVersion;
                            $isPrimary = $index === 0 && $doc->documentType->is_required;
                            $isOptional = !$doc->documentType->is_required;
                        @endphp
                        <div class="border border-slate-200 dark:border-slate-800 rounded-2xl p-5 bg-white dark:bg-slate-950 space-y-4">
                            <div class="flex justify-between items-start gap-4">
                                <div>
                                    <div class="text-sm font-bold text-slate-900 dark:text-white">{{ $doc->documentType->name }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">{{ $doc->documentType->description }}</div>
                                </div>
                                <span class="text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-full shrink-0 {{ $isOptional ? 'text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-800' : 'text-primary bg-primary/10 dark:bg-primary/20' }}">
                                    {{ $isPrimary ? 'Primary' : ($isOptional ? 'Optional' : 'Required') }}
                                </span>
                            </div>

                            <div class="border border-dashed border-slate-300 dark:border-slate-700 rounded-xl p-4 bg-slate-50/50 dark:bg-slate-900/50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                                <div class="min-w-0">
                                    <strong class="text-xs text-slate-900 dark:text-white block truncate font-bold">
                                        {{ $latest ? $latest->file_name : 'No file selected' }}
                                    </strong>
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400 block mt-0.5">
                                        @if($latest)
                                            {{ round($latest->file_size / 1024 / 1024, 2) }} MB · {{ ucwords(str_replace('_', ' ', $latest->status)) }}
                                        @else
                                            PDF, JPG or PNG · Up to 10 MB
                                        @endif
                                    </span>
                                </div>

                                <div class="flex items-center gap-2">
                                    @if($latest)
                                        <button type="button" wire:click="openHistoryModal('{{ $doc->documentType->id }}')" class="px-3.5 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                                            History
                                        </button>
                                    @endif
                                    <button type="button" wire:click="openUploadModal('{{ $doc->documentType->id }}')" class="px-4 py-2 rounded-lg bg-primary hover:bg-primary/90 text-white font-bold text-xs transition-all shadow-sm">
                                        {{ $latest ? 'Change file' : 'Choose file' }}
                                    </button>
                                </div>
                            </div>

                            @if($latest && $latest->status === 'rejected')
                                <div class="p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900/50 text-xs text-rose-800 dark:text-rose-300">
                                    <strong>Rejection Reason:</strong> {{ ucwords(str_replace('_', ' ', $latest->rejection_reason)) }}
                                    @if($latest->reviewer_notes)
                                        <p class="mt-1 italic text-[11px]">Note: "{{ $latest->reviewer_notes }}"</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="p-6 text-center text-xs text-slate-500 dark:text-slate-400 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl">
                            No document requirements defined for this entity type yet.
                        </div>
                    @endforelse
                </div>

                <div class="flex justify-between items-center pt-4 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" wire:click="goStep(2)" class="px-5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                        Back
                    </button>
                    <button type="button" wire:click="goStep(4)" class="px-6 py-2.5 rounded-xl bg-primary hover:bg-primary/90 text-white font-bold text-xs transition-all shadow-md shadow-primary/20">
                        Review application
                    </button>
                </div>
            </div>
        @endif

        <!-- STEP 4: REVIEW & SUBMIT -->
        @if($currentStep === 4)
            <div class="space-y-6">
                <div class="flex justify-between items-start gap-4">
                    <div>
                        <div class="text-[11px] uppercase tracking-widest font-extrabold text-primary mb-1">Step 4 of 4</div>
                        <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Review before you submit</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Check that the details and documents are accurate. After submission, our team will review your application.</p>
                    </div>
                    <span class="text-xs font-bold text-primary bg-primary/10 dark:bg-primary/20 px-3 py-1 rounded-full whitespace-nowrap">Final check</span>
                </div>

                <!-- Business Review Card -->
                <div class="border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden">
                    <div class="px-5 py-3 bg-slate-50 dark:bg-slate-950 text-xs font-extrabold uppercase tracking-wider text-slate-900 dark:text-white border-b border-slate-200 dark:border-slate-800">Business Details</div>
                    <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div><span class="block text-[10px] font-bold text-slate-400 uppercase mb-0.5">Business type</span><strong class="font-bold text-slate-900 dark:text-white">{{ $business_type ?: '—' }}</strong></div>
                        <div><span class="block text-[10px] font-bold text-slate-400 uppercase mb-0.5">Legal business name</span><strong class="font-bold text-slate-900 dark:text-white">{{ $legal_name ?: '—' }}</strong></div>
                        <div><span class="block text-[10px] font-bold text-slate-400 uppercase mb-0.5">WhatsApp display name</span><strong class="font-bold text-slate-900 dark:text-white">{{ $display_name ?: '—' }}</strong></div>
                        <div><span class="block text-[10px] font-bold text-slate-400 uppercase mb-0.5">Category</span><strong class="font-bold text-slate-900 dark:text-white">{{ $category ?: '—' }}</strong></div>
                        <div><span class="block text-[10px] font-bold text-slate-400 uppercase mb-0.5">Business email</span><strong class="font-bold text-slate-900 dark:text-white">{{ $business_email ?: '—' }}</strong></div>
                        <div><span class="block text-[10px] font-bold text-slate-400 uppercase mb-0.5">WhatsApp number</span><strong class="font-bold text-primary">{{ $wa_phone ?: '—' }}</strong></div>
                        <div><span class="block text-[10px] font-bold text-slate-400 uppercase mb-0.5">Signatory & Designation</span><strong class="font-bold text-slate-900 dark:text-white">{{ $signatory_name ?: '—' }} ({{ $signatory_designation ?: '—' }})</strong></div>
                        <div><span class="block text-[10px] font-bold text-slate-400 uppercase mb-0.5">Website</span><strong class="font-bold text-slate-900 dark:text-white">{{ $website ?: 'None' }}</strong></div>
                    </div>
                </div>

                <!-- Documents Review Card -->
                <div class="border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden">
                    <div class="px-5 py-3 bg-slate-50 dark:bg-slate-950 text-xs font-extrabold uppercase tracking-wider text-slate-900 dark:text-white border-b border-slate-200 dark:border-slate-800">Uploaded Documents</div>
                    <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        @forelse($verification->documents as $idx => $doc)
                            <div>
                                <span class="block text-[10px] font-bold text-slate-400 uppercase mb-0.5">{{ $doc->documentType->name }}</span>
                                <strong class="font-bold text-slate-900 dark:text-white">{{ $doc->latestVersion ? $doc->latestVersion->file_name : 'No file uploaded' }}</strong>
                            </div>
                        @empty
                            <div class="col-span-2 text-xs text-slate-500">No documents attached.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Checkbox Declaration -->
                <label class="flex items-start gap-3 text-xs text-slate-900 dark:text-white leading-relaxed cursor-pointer p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800">
                    <input type="checkbox" wire:model.live="confirmDeclaration" class="mt-0.5 size-4 accent-primary rounded" />
                    <span>I confirm that the information provided is accurate and that I am authorized to submit this business for verification.</span>
                </label>
                @error('confirmDeclaration')
                    <div class="text-xs text-rose-500 font-bold">{{ $message }}</div>
                @enderror

                <div class="p-4 rounded-xl bg-primary/10 dark:bg-primary/20 border border-primary/20 text-xs text-slate-800 dark:text-slate-200 leading-relaxed">
                    Submitting starts our internal review. We may contact you if anything needs clarification before the application is handed off for Meta onboarding.
                </div>

                <div class="flex justify-between items-center pt-4 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" wire:click="goStep(3)" class="px-5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                        Back
                    </button>
                    <button type="button" wire:click="submitApplication" {{ !$confirmDeclaration ? 'disabled' : '' }} class="px-6 py-2.5 rounded-xl bg-primary hover:bg-primary/90 text-white font-bold text-xs transition-all disabled:opacity-40 disabled:cursor-not-allowed shadow-md shadow-primary/20">
                        Submit for verification
                    </button>
                </div>
            </div>
        @endif

        <!-- STEP 5: SUBMITTED TIMELINE & STATUS TRACKER -->
        @if($currentStep === 5)
            <div class="space-y-6 py-2">
                <div class="flex items-center gap-4">
                    <div class="size-14 rounded-2xl bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-black text-2xl shrink-0">
                        ✓
                    </div>
                    <div>
                        <div class="text-[11px] uppercase tracking-widest font-extrabold text-primary mb-0.5">
                            Status: {{ str_replace('_', ' ', strtoupper($verification->status)) }}
                        </div>
                        <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                            @if($verification->status === 'verified')
                                Your business verification is approved!
                            @elseif($verification->status === 'rejected')
                                Changes requested for your verification
                            @else
                                Your verification is now in review
                            @endif
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">We’ve received your business information and documents. You can track the process below.</p>
                    </div>
                </div>

                <!-- Timeline Rows -->
                <div class="divide-y divide-slate-100 dark:divide-slate-800 border-y border-slate-100 dark:border-slate-800 py-2">
                    <div class="py-4 grid grid-cols-[32px_1fr_auto] gap-4 items-start">
                        <div class="size-7 rounded-full bg-primary text-white flex items-center justify-center font-black text-xs">✓</div>
                        <div>
                            <strong class="text-xs text-slate-900 dark:text-white block">Submitted</strong>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 block mt-0.5">Your application was received successfully.</span>
                        </div>
                        <div class="text-[10px] text-slate-400">{{ $verification->submitted_at ? $verification->submitted_at->diffForHumans() : 'Done' }}</div>
                    </div>

                    <div class="py-4 grid grid-cols-[32px_1fr_auto] gap-4 items-start">
                        <div class="size-7 rounded-full text-xs font-black flex items-center justify-center {{ in_array($verification->status, ['under_review', 'partially_approved', 'verified']) ? 'bg-primary text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-500' }}">
                            {{ in_array($verification->status, ['under_review', 'partially_approved', 'verified']) ? '✓' : '2' }}
                        </div>
                        <div>
                            <strong class="text-xs text-slate-900 dark:text-white block">Internal review</strong>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 block mt-0.5">Our team is checking the business information and documents.</span>
                        </div>
                        <div class="text-[10px] text-slate-400 font-bold">
                            {{ $verification->status === 'under_review' ? 'Current' : ($verification->status === 'not_started' ? 'Pending' : 'Done') }}
                        </div>
                    </div>

                    <div class="py-4 grid grid-cols-[32px_1fr_auto] gap-4 items-start">
                        <div class="size-7 rounded-full text-xs font-black flex items-center justify-center {{ $verification->status === 'verified' ? 'bg-primary text-white' : ($verification->status === 'rejected' ? 'bg-rose-500 text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-500') }}">
                            {{ $verification->status === 'verified' ? '✓' : '3' }}
                        </div>
                        <div>
                            <strong class="text-xs text-slate-900 dark:text-white block">Changes requested or ready for Meta</strong>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 block mt-0.5">If anything is missing, we’ll ask you to update it. Otherwise, we’ll prepare the Meta handoff.</span>
                        </div>
                        <div class="text-[10px] text-slate-400 font-bold">
                            {{ $verification->status === 'rejected' ? 'Action Needed' : 'Next' }}
                        </div>
                    </div>

                    <div class="py-4 grid grid-cols-[32px_1fr_auto] gap-4 items-start">
                        <div class="size-7 rounded-full text-xs font-black flex items-center justify-center {{ $verification->status === 'verified' ? 'bg-primary text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-500' }}">
                            {{ $verification->status === 'verified' ? '✓' : '4' }}
                        </div>
                        <div>
                            <strong class="text-xs text-slate-900 dark:text-white block">Meta verification</strong>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 block mt-0.5">The business is submitted to Meta for the relevant onboarding checks.</span>
                        </div>
                        <div class="text-[10px] text-slate-400">Pending</div>
                    </div>

                    <div class="py-4 grid grid-cols-[32px_1fr_auto] gap-4 items-start">
                        <div class="size-7 rounded-full text-xs font-black flex items-center justify-center {{ $verification->status === 'verified' ? 'bg-primary text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-500' }}">
                            {{ $verification->status === 'verified' ? '✓' : '5' }}
                        </div>
                        <div>
                            <strong class="text-xs text-slate-900 dark:text-white block">Number registration & completion</strong>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 block mt-0.5">Once approved, the WhatsApp number can complete platform registration.</span>
                        </div>
                        <div class="text-[10px] text-slate-400">{{ $verification->status === 'verified' ? 'Completed' : 'Pending' }}</div>
                    </div>
                </div>

                <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                    <strong class="text-slate-900 dark:text-white block mb-1">What happens next?</strong>
                    Keep your business information and documents available. If our reviewer requests changes, you’ll be able to update only the affected items instead of starting the whole application again.
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="button" wire:click="goStep(3)" class="px-5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-200 font-bold text-xs hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                        Manage / Upload Documents
                    </button>
                    <button type="button" wire:click="goStep(2)" class="px-5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-200 font-bold text-xs hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                        Edit Business Info
                    </button>
                </div>
            </div>
        @endif
    </div>

    <!-- Upload Modal -->
    @if($showUploadModal)
        @php
            $targetDocType = \App\Models\DocumentType::find($selectedDocTypeId);
        @endphp
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md shadow-2xl overflow-hidden flex flex-col">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-950">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Upload Document: {{ $targetDocType ? $targetDocType->name : '' }}</h3>
                    <button type="button" wire:click="closeUploadModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 font-bold">✕</button>
                </div>

                <form wire:submit.prevent="submitDocument">
                    <div class="p-6 space-y-4">
                        <div class="text-xs text-slate-500 dark:text-slate-400">
                            Format: PDF, JPG or PNG up to 10 MB.
                        </div>

                        <div class="border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-xl p-6 text-center bg-slate-50/50 dark:bg-slate-950">
                            <input type="file" id="onboard_file_input" class="hidden" wire:model="file" accept=".pdf,.jpg,.jpeg,.png" />
                            <label for="onboard_file_input" class="cursor-pointer">
                                <span class="material-symbols-outlined text-3xl text-primary mb-1 block">upload_file</span>
                                <span class="block text-xs font-bold text-primary mb-1">Click to select file</span>
                                <span class="text-[10px] text-slate-400 block">Accepted: PDF, JPG, PNG</span>
                            </label>

                            @if($file)
                                <div class="mt-3 p-2.5 bg-white dark:bg-slate-900 rounded border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-900 dark:text-white truncate">
                                    {{ $file->getClientOriginalName() }} ({{ round($file->getSize() / 1024 / 1024, 2) }} MB)
                                </div>
                            @endif
                        </div>
                        @error('file') <span class="text-xs text-rose-500 font-bold block">{{ $message }}</span> @enderror

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-900 dark:text-white mb-1">Issue Date (Optional)</label>
                                <input type="date" wire:model="issueDate" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2 text-xs text-slate-900 dark:text-white" />
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-900 dark:text-white mb-1">Expiry Date (Optional)</label>
                                <input type="date" wire:model="expiryDate" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2 text-xs text-slate-900 dark:text-white" />
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-950 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-2">
                        <button type="button" wire:click="closeUploadModal" class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-primary hover:bg-primary/90 text-white font-bold text-xs" wire:loading.attr="disabled" wire:target="file">
                            Upload file
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- History Modal -->
    @if($showHistoryModal && $selectedDocTypeForHistory)
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden flex flex-col">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-950">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Version History: {{ $selectedDocTypeForHistory->name }}</h3>
                    <button type="button" wire:click="closeHistoryModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 font-bold">✕</button>
                </div>
                <div class="p-6 overflow-y-auto max-h-[60vh] space-y-3">
                    @forelse($historyDocumentsList as $v)
                        <div class="p-4 border border-slate-200 dark:border-slate-800 rounded-xl bg-slate-50/50 dark:bg-slate-950 text-xs space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-slate-900 dark:text-white">Version {{ $v->version_number }}</span>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase text-primary bg-primary/10 dark:bg-primary/20">
                                    {{ str_replace('_', ' ', $v->status) }}
                                </span>
                            </div>
                            <div class="text-slate-500 dark:text-slate-400 text-[11px]">Submitted by {{ $v->uploader?->name ?? 'User' }} on {{ $v->created_at->format('Y-m-d H:i') }}</div>
                            @if($v->rejection_reason)
                                <div class="mt-1 text-rose-600 font-bold">Rejection Reason: {{ ucwords(str_replace('_', ' ', $v->rejection_reason)) }}</div>
                            @endif
                            <div class="pt-2 flex justify-end">
                                <a href="{{ $v->getDownloadUrl() }}" target="_blank" class="px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs hover:bg-slate-100 dark:hover:bg-slate-800">Download</a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-xs text-slate-500 dark:text-slate-400 py-4">No uploaded versions found.</div>
                    @endforelse
                </div>
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-950 border-t border-slate-200 dark:border-slate-800 flex justify-end">
                    <button type="button" wire:click="closeHistoryModal" class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs">Close</button>
                </div>
            </div>
        </div>
    @endif
</div>

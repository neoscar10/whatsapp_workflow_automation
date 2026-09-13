<div class="min-h-screen bg-[#f6f8f7] text-[#17201c] py-8 px-4 sm:px-6 lg:px-8 font-sans">
    <style>
        :root {
            --bg: #f6f8f7; --surface: #ffffff; --surface-2: #f9fbfa; --line: #e3e9e6;
            --text: #17201c; --muted: #68736e; --green: #1f9d68; --green-dark: #15764e;
            --green-soft: #e9f7f0; --danger: #c94b4b; --danger-soft: #fff1f1;
            --warning: #9a6a19; --warning-soft: #fff8e7; --shadow: 0 18px 50px rgba(20,35,28,.07);
        }
        .onboard-shell { max-width: 1180px; margin: auto; }
        .onboard-brand { display: flex; align-items: center; gap: 10px; font-weight: 800; letter-spacing: -.02em; margin-bottom: 30px; }
        .onboard-brand-mark { width: 36px; height: 36px; border-radius: 10px; background: var(--green); display: grid; place-items: center; color: white; font-weight: 900; }
        .onboard-card { background: var(--surface); border: 1px solid var(--line); border-radius: 18px; box-shadow: var(--shadow); }
        .onboard-step-item { display: flex; gap: 13px; padding: 12px 0; position: relative; }
        .onboard-step-item:not(:last-child):after { content: ""; position: absolute; left: 14px; top: 40px; width: 1px; height: 25px; background: var(--line); }
        .onboard-dot { width: 29px; height: 29px; border-radius: 50%; border: 1px solid #ccd6d1; background: white; display: grid; place-items: center; font-size: 12px; font-weight: 800; flex: none; z-index: 1; color: var(--muted); }
        .onboard-step-item.active .onboard-dot { border-color: var(--green); background: var(--green); color: white; }
        .onboard-step-item.done .onboard-dot { border-color: var(--green); background: var(--green-soft); color: var(--green-dark); }
        .entity-btn { border: 1px solid var(--line); border-radius: 14px; padding: 17px; text-align: left; background: white; transition: all .15s ease; cursor: pointer; }
        .entity-btn:hover { border-color: #b8c9c1; transform: translateY(-1px); }
        .entity-btn.selected { border-color: var(--green); box-shadow: 0 0 0 3px rgba(31,157,104,.12); background: #fbfefc; }
        .entity-icon { width: 35px; height: 35px; border-radius: 10px; background: var(--surface-2); display: grid; place-items: center; margin-bottom: 12px; font-weight: 850; color: var(--green-dark); font-size: 13px; }
        .onboard-input { width: 100%; border: 1px solid #d7e0dc; border-radius: 10px; background: white; color: var(--text); padding: 11px 13px; font-size: 13px; outline: none; transition: border-color .15s, box-shadow .15s; }
        .onboard-input:focus { border-color: var(--green); box-shadow: 0 0 0 3px rgba(31,157,104,.1); }
        .onboard-btn { border: 1px solid #d3ddd8; background: white; border-radius: 10px; padding: 11px 18px; font-size: 13px; font-weight: 800; color: var(--text); transition: all .15s; cursor: pointer; display: inline-flex; align-items: center; justify-center: center; }
        .onboard-btn:hover:not(:disabled) { background: #f0f4f2; }
        .onboard-btn-primary { background: var(--green); border-color: var(--green); color: white; }
        .onboard-btn-primary:hover:not(:disabled) { background: var(--green-dark); border-color: var(--green-dark); }
        .onboard-btn:disabled { opacity: .45; cursor: not-allowed; }
        .timeline-row { display: grid; grid-template-columns: 26px 1fr auto; gap: 14px; align-items: start; padding: 14px 0; border-bottom: 1px solid var(--line); }
        .timeline-row:last-child { border-bottom: 0; }
        .tl-dot { width: 24px; height: 24px; border-radius: 50%; display: grid; place-items: center; font-size: 11px; font-weight: 900; border: 1px solid var(--line); color: var(--muted); }
        .tl-dot.done { background: var(--green); color: white; border-color: var(--green); }
        .tl-dot.current { background: var(--green-soft); color: var(--green-dark); border-color: #bce4d2; }
    </style>

    <div class="onboard-shell">
        <!-- Brand Header -->
        <div class="onboard-brand">
            <div class="onboard-brand-mark">C</div>
            <div>
                <span class="text-base font-extrabold tracking-tight">CloudFlow</span>
                <small class="block text-[11px] text-[#68736e] font-semibold tracking-wider uppercase mt-0.5">WhatsApp Business Platform</small>
            </div>
        </div>

        <!-- Session Flash Messages -->
        @if (session()->has('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center justify-between">
                <span>{{ session('success') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900">✕</button>
            </div>
        @endif

        <!-- Main Title Header -->
        <div class="flex flex-col md:flex-row justify-between items-start gap-4 mb-7">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-[#17201c] m-0 leading-tight">Business Verification</h1>
                <p class="mt-2 text-sm text-[#68736e] max-w-3xl leading-relaxed">
                    Tell us about your business and provide the official documents that apply to your entity. We’ll review the information before your business is submitted for WhatsApp onboarding.
                </p>
            </div>
            <div class="text-xs text-[#68736e] whitespace-nowrap bg-white border border-[#e3e9e6] px-3.5 py-2 rounded-full font-bold shadow-sm self-start md:self-auto">
                🔒 Secure onboarding · Human review
            </div>
        </div>

        <!-- Onboarding Layout (Left Navigation + Right Workspace) -->
        <div class="grid grid-cols-1 lg:grid-cols-[280px_1fr] gap-6 items-start">
            <!-- Left Progress Column -->
            <aside class="onboard-card p-5 sticky top-5">
                <div class="text-xs uppercase tracking-widest font-extrabold text-[#68736e] mb-4">Verification progress</div>
                
                <div class="onboard-step-item {{ $currentStep === 1 ? 'active' : ($currentStep > 1 ? 'done' : '') }}">
                    <div class="onboard-dot">{{ $currentStep > 1 ? '✓' : '1' }}</div>
                    <div>
                        <div class="text-xs font-bold leading-tight">Business type</div>
                        <div class="text-[11px] text-[#68736e] mt-0.5">Choose your entity</div>
                    </div>
                </div>

                <div class="onboard-step-item {{ $currentStep === 2 ? 'active' : ($currentStep > 2 ? 'done' : '') }}">
                    <div class="onboard-dot">{{ $currentStep > 2 ? '✓' : '2' }}</div>
                    <div>
                        <div class="text-xs font-bold leading-tight">Business details</div>
                        <div class="text-[11px] text-[#68736e] mt-0.5">Tell us about it</div>
                    </div>
                </div>

                <div class="onboard-step-item {{ $currentStep === 3 ? 'active' : ($currentStep > 3 ? 'done' : '') }}">
                    <div class="onboard-dot">{{ $currentStep > 3 ? '✓' : '3' }}</div>
                    <div>
                        <div class="text-xs font-bold leading-tight">Documents</div>
                        <div class="text-[11px] text-[#68736e] mt-0.5">Upload evidence</div>
                    </div>
                </div>

                <div class="onboard-step-item {{ $currentStep === 4 ? 'active' : ($currentStep > 4 ? 'done' : '') }}">
                    <div class="onboard-dot">{{ $currentStep > 4 ? '✓' : '4' }}</div>
                    <div>
                        <div class="text-xs font-bold leading-tight">Review & submit</div>
                        <div class="text-[11px] text-[#68736e] mt-0.5">Check and send</div>
                    </div>
                </div>
            </aside>

            <!-- Right Workspace Card -->
            <main class="onboard-card p-6 md:p-8">
                <!-- STEP 1: BUSINESS TYPE SELECTION -->
                @if($currentStep === 1)
                    <div>
                        <div class="flex justify-between items-start gap-4 mb-6">
                            <div>
                                <div class="text-[11px] uppercase tracking-widest font-extrabold text-[#15764e] mb-1.5">Step 1 of 4</div>
                                <h2 class="text-2xl font-bold tracking-tight text-[#17201c] m-0">What type of business are you?</h2>
                                <p class="text-xs text-[#68736e] mt-1">Your selection determines the business evidence we’ll ask you for next.</p>
                            </div>
                            <span class="text-xs font-bold text-[#15764e] bg-[#e9f7f0] px-3 py-1 rounded-full whitespace-nowrap">Required</span>
                        </div>

                        @error('business_type')
                            <div class="mb-4 text-xs font-bold text-rose-600 bg-rose-50 border border-rose-200 p-3 rounded-xl">{{ $message }}</div>
                        @enderror

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-5">
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
                                <button type="button" wire:click="selectEntityType('{{ $name }}')" class="entity-btn {{ $business_type === $name ? 'selected' : '' }}">
                                    <div class="entity-icon">{{ $info['code'] }}</div>
                                    <strong class="block text-sm font-bold text-[#17201c] mb-1">{{ $name }}</strong>
                                    <span class="text-xs text-[#68736e] leading-snug block">{{ $info['desc'] }}</span>
                                </button>
                            @endforeach
                        </div>

                        <div class="p-3.5 rounded-xl bg-[#f9fbfa] border border-[#e3e9e6] text-xs text-[#68736e] leading-relaxed mb-7">
                            <strong class="text-[#17201c]">Why we ask:</strong> Different business structures have different official records. We’ll show document options relevant to the business type you select.
                        </div>

                        <div class="flex justify-end pt-5 border-t border-[#e3e9e6]">
                            <button type="button" wire:click="goStep(2)" {{ !$business_type ? 'disabled' : '' }} class="onboard-btn onboard-btn-primary">
                                Continue
                            </button>
                        </div>
                    </div>
                @endif

                <!-- STEP 2: BUSINESS DETAILS FORM -->
                @if($currentStep === 2)
                    <div>
                        <div class="flex justify-between items-start gap-4 mb-6">
                            <div>
                                <div class="text-[11px] uppercase tracking-widest font-extrabold text-[#15764e] mb-1.5">Step 2 of 4</div>
                                <h2 class="text-2xl font-bold tracking-tight text-[#17201c] m-0">Tell us about your business</h2>
                                <p class="text-xs text-[#68736e] mt-1">Use your legal business information where requested. Your display name can be customer-facing.</p>
                            </div>
                            <span class="text-xs font-bold text-[#15764e] bg-[#e9f7f0] px-3 py-1 rounded-full whitespace-nowrap">{{ $business_type ?: 'Business Details' }}</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-xs font-extrabold text-[#17201c] mb-1.5">Legal business name <span class="text-rose-600">*</span></label>
                                <input type="text" wire:model="legal_name" placeholder="Exactly as shown on official records" class="onboard-input" />
                                <span class="text-[11px] text-[#68736e] mt-1 block">This should match the name on your supporting business document.</span>
                                @error('legal_name') <span class="text-xs text-rose-600 font-semibold mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-extrabold text-[#17201c] mb-1.5">WhatsApp display name <span class="text-rose-600">*</span></label>
                                <input type="text" wire:model="display_name" placeholder="Name customers will see" class="onboard-input" />
                                @error('display_name') <span class="text-xs text-rose-600 font-semibold mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-extrabold text-[#17201c] mb-1.5">Business category / industry <span class="text-rose-600">*</span></label>
                                <select wire:model="category" class="onboard-input">
                                    <option value="">Select a category</option>
                                    <option value="Retail">Retail</option>
                                    <option value="Technology">Technology</option>
                                    <option value="Professional services">Professional services</option>
                                    <option value="Education">Education</option>
                                    <option value="Food & hospitality">Food & hospitality</option>
                                    <option value="Health & wellness">Health & wellness</option>
                                    <option value="Other">Other</option>
                                </select>
                                @error('category') <span class="text-xs text-rose-600 font-semibold mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-extrabold text-[#17201c] mb-1.5">Business website</label>
                                <input type="url" wire:model="website" placeholder="https://example.com" class="onboard-input" />
                                <span class="text-[11px] text-[#68736e] mt-1 block">If you don’t have a website, you can leave this blank.</span>
                                @error('website') <span class="text-xs text-rose-600 font-semibold mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-extrabold text-[#17201c] mb-1.5">Registered business address <span class="text-rose-600">*</span></label>
                                <textarea wire:model="address" rows="3" placeholder="Enter the official registered or business address" class="onboard-input resize-y min-h-[80px]"></textarea>
                                @error('address') <span class="text-xs text-rose-600 font-semibold mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-extrabold text-[#17201c] mb-1.5">Authorized signatory <span class="text-rose-600">*</span></label>
                                <input type="text" wire:model="signatory_name" placeholder="Full name" class="onboard-input" />
                                @error('signatory_name') <span class="text-xs text-rose-600 font-semibold mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-extrabold text-[#17201c] mb-1.5">Designation / role <span class="text-rose-600">*</span></label>
                                <input type="text" wire:model="signatory_designation" placeholder="e.g. Director, Owner, Partner" class="onboard-input" />
                                @error('signatory_designation') <span class="text-xs text-rose-600 font-semibold mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-extrabold text-[#17201c] mb-1.5">Business email <span class="text-rose-600">*</span></label>
                                <input type="email" wire:model="business_email" placeholder="name@business.com" class="onboard-input" />
                                @error('business_email') <span class="text-xs text-rose-600 font-semibold mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-extrabold text-[#17201c] mb-1.5">Business phone <span class="text-rose-600">*</span></label>
                                <input type="text" wire:model="business_phone" placeholder="+234 ..." class="onboard-input" />
                                @error('business_phone') <span class="text-xs text-rose-600 font-semibold mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-extrabold text-[#17201c] mb-1.5">WhatsApp phone number to register <span class="text-rose-600">*</span></label>
                                <input type="text" wire:model="wa_phone" placeholder="+234 ..." class="onboard-input" />
                                <span class="text-[11px] text-[#68736e] mt-1 block">This is the number you intend to connect to the WhatsApp Business Platform.</span>
                                @error('wa_phone') <span class="text-xs text-rose-600 font-semibold mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="p-3.5 rounded-xl bg-[#fff8e7] border border-[#f0dfb1] text-xs text-[#9a6a19] leading-relaxed mb-7">
                            <strong>Before you continue:</strong> If this number is already active on WhatsApp or WhatsApp Business, additional eligibility or migration steps may apply.
                        </div>

                        <div class="flex justify-between items-center pt-5 border-t border-[#e3e9e6]">
                            <button type="button" wire:click="goStep(1)" class="onboard-btn">Back</button>
                            <button type="button" wire:click="goStep(3)" class="onboard-btn onboard-btn-primary">Continue</button>
                        </div>
                    </div>
                @endif

                <!-- STEP 3: DOCUMENT UPLOADS -->
                @if($currentStep === 3)
                    <div>
                        <div class="flex justify-between items-start gap-4 mb-5">
                            <div>
                                <div class="text-[11px] uppercase tracking-widest font-extrabold text-[#15764e] mb-1.5">Step 3 of 4</div>
                                <h2 class="text-2xl font-bold tracking-tight text-[#17201c] m-0">Provide your business documents</h2>
                                <p class="text-xs text-[#68736e] mt-1">Upload clear, valid documents that support the business information you entered. The options below change based on your business type.</p>
                            </div>
                            <span class="text-xs font-bold text-[#15764e] bg-[#e9f7f0] px-3 py-1 rounded-full whitespace-nowrap">{{ $business_type }}</span>
                        </div>

                        <div class="p-3.5 rounded-xl bg-[#e9f7f0] border border-[#ccebdd] text-xs text-[#245c45] leading-relaxed mb-4">
                            <strong>Keep it simple:</strong> Choose the official document that best proves the requested information. We may ask for another document during review if the evidence is incomplete or unclear.
                        </div>

                        <div class="text-[11px] text-[#68736e] mb-5">Accepted files: PDF, JPG or PNG · Maximum 10 MB per file · Documents should be readable and in English.</div>

                        <!-- Dynamic Document List -->
                        <div class="space-y-4 mb-7">
                            @forelse($verification->documents as $index => $doc)
                                @php
                                    $latest = $doc->latestVersion;
                                    $isPrimary = $index === 0 && $doc->documentType->is_required;
                                    $isOptional = !$doc->documentType->is_required;
                                @endphp
                                <div class="border border-[#e3e9e6] rounded-2xl p-4 sm:p-5 bg-white">
                                    <div class="flex justify-between items-start gap-4 mb-3">
                                        <div>
                                            <div class="text-sm font-extrabold text-[#17201c]">{{ $doc->documentType->name }}</div>
                                            <div class="text-xs text-[#68736e] mt-0.5 leading-snug">{{ $doc->documentType->description }}</div>
                                        </div>
                                        <span class="text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-full shrink-0 {{ $isOptional ? 'text-[#68736e] bg-[#f1f4f2]' : 'text-[#15764e] bg-[#e9f7f0]' }}">
                                            {{ $isPrimary ? 'Primary' : ($isOptional ? 'Optional' : 'Required') }}
                                        </span>
                                    </div>

                                    <div class="mt-3 border border-dashed border-[#bdcbc4] rounded-xl p-3.5 bg-[#f9fbfa] flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                                        <div class="min-w-0">
                                            <strong class="text-xs text-[#17201c] block truncate font-bold">
                                                {{ $latest ? $latest->file_name : 'No file selected' }}
                                            </strong>
                                            <span class="text-[10px] text-[#68736e] block mt-0.5">
                                                @if($latest)
                                                    {{ round($latest->file_size / 1024 / 1024, 2) }} MB · {{ ucwords(str_replace('_', ' ', $latest->status)) }}
                                                @else
                                                    PDF, JPG or PNG · Up to 10 MB
                                                @endif
                                            </span>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            @if($latest)
                                                <button type="button" wire:click="openHistoryModal('{{ $doc->documentType->id }}')" class="onboard-btn py-1.5 px-3 text-xs">
                                                    History
                                                </button>
                                            @endif
                                            <button type="button" wire:click="openUploadModal('{{ $doc->documentType->id }}')" class="onboard-btn py-1.5 px-3 text-xs">
                                                {{ $latest ? 'Change file' : 'Choose file' }}
                                            </button>
                                        </div>
                                    </div>

                                    @if($latest && $latest->status === 'rejected')
                                        <div class="mt-3 p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-800">
                                            <strong>Rejection Reason:</strong> {{ ucwords(str_replace('_', ' ', $latest->rejection_reason)) }}
                                            @if($latest->reviewer_notes)
                                                <p class="mt-1 italic text-[11px]">Note: "{{ $latest->reviewer_notes }}"</p>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="p-6 text-center text-xs text-[#68736e] bg-white border border-[#e3e9e6] rounded-xl">
                                    No document requirements defined for this entity type yet.
                                </div>
                            @endforelse
                        </div>

                        <div class="flex justify-between items-center pt-5 border-t border-[#e3e9e6]">
                            <button type="button" wire:click="goStep(2)" class="onboard-btn">Back</button>
                            <button type="button" wire:click="goStep(4)" class="onboard-btn onboard-btn-primary">Review application</button>
                        </div>
                    </div>
                @endif

                <!-- STEP 4: REVIEW & SUBMIT -->
                @if($currentStep === 4)
                    <div>
                        <div class="flex justify-between items-start gap-4 mb-6">
                            <div>
                                <div class="text-[11px] uppercase tracking-widest font-extrabold text-[#15764e] mb-1.5">Step 4 of 4</div>
                                <h2 class="text-2xl font-bold tracking-tight text-[#17201c] m-0">Review before you submit</h2>
                                <p class="text-xs text-[#68736e] mt-1">Check that the details and documents are accurate. After submission, our team will review your application.</p>
                            </div>
                            <span class="text-xs font-bold text-[#15764e] bg-[#e9f7f0] px-3 py-1 rounded-full whitespace-nowrap">Final check</span>
                        </div>

                        <!-- Business Review Card -->
                        <div class="border border-[#e3e9e6] rounded-2xl mb-4 overflow-hidden">
                            <div class="px-4 py-3 bg-[#f9fbfa] text-[11px] font-extrabold uppercase tracking-wider text-[#17201c] border-b border-[#e3e9e6]">Business</div>
                            <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                                <div><span class="block text-[10px] text-[#68736e] mb-0.5">Business type</span><strong class="font-bold text-[#17201c]">{{ $business_type ?: '—' }}</strong></div>
                                <div><span class="block text-[10px] text-[#68736e] mb-0.5">Legal business name</span><strong class="font-bold text-[#17201c]">{{ $legal_name ?: '—' }}</strong></div>
                                <div><span class="block text-[10px] text-[#68736e] mb-0.5">WhatsApp display name</span><strong class="font-bold text-[#17201c]">{{ $display_name ?: '—' }}</strong></div>
                                <div><span class="block text-[10px] text-[#68736e] mb-0.5">Category</span><strong class="font-bold text-[#17201c]">{{ $category ?: '—' }}</strong></div>
                                <div><span class="block text-[10px] text-[#68736e] mb-0.5">Business email</span><strong class="font-bold text-[#17201c]">{{ $business_email ?: '—' }}</strong></div>
                                <div><span class="block text-[10px] text-[#68736e] mb-0.5">WhatsApp number</span><strong class="font-bold text-[#17201c]">{{ $wa_phone ?: '—' }}</strong></div>
                                <div><span class="block text-[10px] text-[#68736e] mb-0.5">Signatory & Designation</span><strong class="font-bold text-[#17201c]">{{ $signatory_name ?: '—' }} ({{ $signatory_designation ?: '—' }})</strong></div>
                                <div><span class="block text-[10px] text-[#68736e] mb-0.5">Website</span><strong class="font-bold text-[#17201c]">{{ $website ?: 'None' }}</strong></div>
                            </div>
                        </div>

                        <!-- Documents Review Card -->
                        <div class="border border-[#e3e9e6] rounded-2xl mb-5 overflow-hidden">
                            <div class="px-4 py-3 bg-[#f9fbfa] text-[11px] font-extrabold uppercase tracking-wider text-[#17201c] border-b border-[#e3e9e6]">Documents</div>
                            <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                                @forelse($verification->documents as $idx => $doc)
                                    <div>
                                        <span class="block text-[10px] text-[#68736e] mb-0.5">{{ $doc->documentType->name }}</span>
                                        <strong class="font-bold text-[#17201c]">{{ $doc->latestVersion ? $doc->latestVersion->file_name : 'No file uploaded' }}</strong>
                                    </div>
                                @empty
                                    <div class="col-span-2 text-xs text-[#68736e]">No documents attached.</div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Checkbox Declaration -->
                        <label class="flex items-start gap-2.5 text-xs text-[#17201c] leading-relaxed mb-4 cursor-pointer">
                            <input type="checkbox" wire:model.live="confirmDeclaration" class="mt-0.5 accent-[#1f9d68]" />
                            <span>I confirm that the information provided is accurate and that I am authorized to submit this business for verification.</span>
                        </label>
                        @error('confirmDeclaration')
                            <div class="text-xs text-rose-600 font-bold mb-4">{{ $message }}</div>
                        @enderror

                        <div class="p-3.5 rounded-xl bg-[#e9f7f0] border border-[#ccebdd] text-xs text-[#245c45] leading-relaxed mb-7">
                            Submitting starts our internal review. We may contact you if anything needs clarification before the application is handed off for Meta onboarding.
                        </div>

                        <div class="flex justify-between items-center pt-5 border-t border-[#e3e9e6]">
                            <button type="button" wire:click="goStep(3)" class="onboard-btn">Back</button>
                            <button type="button" wire:click="submitApplication" {{ !$confirmDeclaration ? 'disabled' : '' }} class="onboard-btn onboard-btn-primary">
                                Submit for verification
                            </button>
                        </div>
                    </div>
                @endif

                <!-- STEP 5: SUBMITTED TIMELINE & STATUS TRACKER -->
                @if($currentStep === 5)
                    <div class="py-2">
                        <div class="w-14 h-14 rounded-2xl bg-[#e9f7f0] text-[#15764e] flex items-center justify-center font-black text-2xl mb-4">
                            ✓
                        </div>
                        <div class="text-[11px] uppercase tracking-widest font-extrabold text-[#15764e] mb-1">
                            Status: {{ str_replace('_', ' ', strtoupper($verification->status)) }}
                        </div>
                        <h2 class="text-2xl font-bold tracking-tight text-[#17201c] m-0 mb-1">
                            @if($verification->status === 'verified')
                                Your business verification is approved!
                            @elseif($verification->status === 'rejected')
                                Changes requested for your verification
                            @else
                                Your verification is now in review
                            @endif
                        </h2>
                        <p class="text-xs text-[#68736e] leading-relaxed">We’ve received your business information and documents. You can track the process below.</p>

                        <!-- Timeline Rows -->
                        <div class="my-6">
                            <div class="timeline-row">
                                <div class="tl-dot done">✓</div>
                                <div>
                                    <strong class="text-xs text-[#17201c] block">Submitted</strong>
                                    <span class="text-[11px] text-[#68736e] block mt-0.5 leading-snug">Your application was received successfully.</span>
                                </div>
                                <div class="text-[10px] text-[#68736e]">{{ $verification->submitted_at ? $verification->submitted_at->diffForHumans() : 'Done' }}</div>
                            </div>

                            <div class="timeline-row">
                                <div class="tl-dot {{ in_array($verification->status, ['under_review', 'partially_approved', 'verified']) ? 'done' : 'current' }}">
                                    {{ in_array($verification->status, ['under_review', 'partially_approved', 'verified']) ? '✓' : '2' }}
                                </div>
                                <div>
                                    <strong class="text-xs text-[#17201c] block">Internal review</strong>
                                    <span class="text-[11px] text-[#68736e] block mt-0.5 leading-snug">Our team is checking the business information and documents.</span>
                                </div>
                                <div class="text-[10px] text-[#68736e]">
                                    {{ $verification->status === 'under_review' ? 'Current' : ($verification->status === 'not_started' ? 'Pending' : 'Done') }}
                                </div>
                            </div>

                            <div class="timeline-row">
                                <div class="tl-dot {{ $verification->status === 'verified' ? 'done' : ($verification->status === 'rejected' ? 'current' : '') }}">
                                    {{ $verification->status === 'verified' ? '✓' : '3' }}
                                </div>
                                <div>
                                    <strong class="text-xs text-[#17201c] block">Changes requested or ready for Meta</strong>
                                    <span class="text-[11px] text-[#68736e] block mt-0.5 leading-snug">If anything is missing, we’ll ask you to update it. Otherwise, we’ll prepare the Meta handoff.</span>
                                </div>
                                <div class="text-[10px] text-[#68736e]">
                                    {{ $verification->status === 'rejected' ? 'Action Needed' : 'Next' }}
                                </div>
                            </div>

                            <div class="timeline-row">
                                <div class="tl-dot {{ $verification->status === 'verified' ? 'done' : '' }}">
                                    {{ $verification->status === 'verified' ? '✓' : '4' }}
                                </div>
                                <div>
                                    <strong class="text-xs text-[#17201c] block">Meta verification</strong>
                                    <span class="text-[11px] text-[#68736e] block mt-0.5 leading-snug">The business is submitted to Meta for the relevant onboarding checks.</span>
                                </div>
                                <div class="text-[10px] text-[#68736e]">Pending</div>
                            </div>

                            <div class="timeline-row">
                                <div class="tl-dot {{ $verification->status === 'verified' ? 'done' : '' }}">
                                    {{ $verification->status === 'verified' ? '✓' : '5' }}
                                </div>
                                <div>
                                    <strong class="text-xs text-[#17201c] block">Number registration & completion</strong>
                                    <span class="text-[11px] text-[#68736e] block mt-0.5 leading-snug">Once approved, the WhatsApp number can complete platform registration.</span>
                                </div>
                                <div class="text-[10px] text-[#68736e]">{{ $verification->status === 'verified' ? 'Completed' : 'Pending' }}</div>
                            </div>
                        </div>

                        <div class="p-4 rounded-xl border border-[#e3e9e6] bg-[#f9fbfa] text-xs text-[#68736e] leading-relaxed mb-6">
                            <strong class="text-[#17201c] block mb-1">What happens next?</strong>
                            Keep your business information and documents available. If our reviewer requests changes, you’ll be able to update only the affected items instead of starting the whole application again.
                        </div>

                        <div class="flex gap-3">
                            <button type="button" wire:click="goStep(3)" class="onboard-btn text-xs">
                                Manage / Upload Documents
                            </button>
                            <button type="button" wire:click="goStep(2)" class="onboard-btn text-xs">
                                Edit Business Info
                            </button>
                        </div>
                    </div>
                @endif
            </main>
        </div>
    </div>

    <!-- Upload Modal -->
    @if($showUploadModal)
        @php
            $targetDocType = \App\Models\DocumentType::find($selectedDocTypeId);
        @endphp
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl w-full max-w-md shadow-xl overflow-hidden flex flex-col">
                <div class="px-6 py-4 border-b border-[#e3e9e6] flex justify-between items-center bg-[#f9fbfa]">
                    <h3 class="text-sm font-bold text-[#17201c]">Upload Document: {{ $targetDocType ? $targetDocType->name : '' }}</h3>
                    <button type="button" wire:click="closeUploadModal" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
                </div>

                <form wire:submit.prevent="submitDocument">
                    <div class="p-6 space-y-4">
                        <div class="text-xs text-[#68736e]">
                            Format: PDF, JPG or PNG up to 10 MB.
                        </div>

                        <div class="border-2 border-dashed border-[#bdcbc4] rounded-xl p-6 text-center bg-[#f9fbfa]">
                            <input type="file" id="onboard_file_input" class="hidden" wire:model="file" accept=".pdf,.jpg,.jpeg,.png" />
                            <label for="onboard_file_input" class="cursor-pointer">
                                <span class="block text-xs font-bold text-[#15764e] mb-1">Click to select file</span>
                                <span class="text-[10px] text-[#68736e] block">Accepted: PDF, JPG, PNG</span>
                            </label>

                            @if($file)
                                <div class="mt-3 p-2 bg-white rounded border border-[#e3e9e6] text-xs font-bold text-[#17201c] truncate">
                                    {{ $file->getClientOriginalName() }} ({{ round($file->getSize() / 1024 / 1024, 2) }} MB)
                                </div>
                            @endif
                        </div>
                        @error('file') <span class="text-xs text-rose-600 font-bold block">{{ $message }}</span> @enderror

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-[#17201c] mb-1">Issue Date (Optional)</label>
                                <input type="date" wire:model="issueDate" class="onboard-input py-1.5" />
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-[#17201c] mb-1">Expiry Date (Optional)</label>
                                <input type="date" wire:model="expiryDate" class="onboard-input py-1.5" />
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-[#f9fbfa] border-t border-[#e3e9e6] flex justify-end gap-2">
                        <button type="button" wire:click="closeUploadModal" class="onboard-btn text-xs py-2">Cancel</button>
                        <button type="submit" class="onboard-btn onboard-btn-primary text-xs py-2" wire:loading.attr="disabled" wire:target="file">
                            Upload file
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- History Modal -->
    @if($showHistoryModal && $selectedDocTypeForHistory)
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl w-full max-w-lg shadow-xl overflow-hidden flex flex-col">
                <div class="px-6 py-4 border-b border-[#e3e9e6] flex justify-between items-center bg-[#f9fbfa]">
                    <h3 class="text-sm font-bold text-[#17201c]">Version History: {{ $selectedDocTypeForHistory->name }}</h3>
                    <button type="button" wire:click="closeHistoryModal" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
                </div>
                <div class="p-6 overflow-y-auto max-h-[60vh] space-y-3">
                    @forelse($historyDocumentsList as $v)
                        <div class="p-3 border border-[#e3e9e6] rounded-xl bg-[#f9fbfa] text-xs space-y-1">
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-[#17201c]">Version {{ $v->version_number }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase text-[#15764e] bg-[#e9f7f0]">
                                    {{ str_replace('_', ' ', $v->status) }}
                                </span>
                            </div>
                            <div class="text-[#68736e] text-[11px]">Submitted by {{ $v->uploader?->name ?? 'User' }} on {{ $v->created_at->format('Y-m-d H:i') }}</div>
                            @if($v->rejection_reason)
                                <div class="mt-1 text-rose-700 font-bold">Rejection Reason: {{ ucwords(str_replace('_', ' ', $v->rejection_reason)) }}</div>
                            @endif
                            <div class="pt-2 flex justify-end">
                                <a href="{{ $v->getDownloadUrl() }}" target="_blank" class="onboard-btn py-1 px-2.5 text-[11px]">Download</a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-xs text-[#68736e] py-4">No uploaded versions found.</div>
                    @endforelse
                </div>
                <div class="px-6 py-4 bg-[#f9fbfa] border-t border-[#e3e9e6] flex justify-end">
                    <button type="button" wire:click="closeHistoryModal" class="onboard-btn text-xs py-1.5">Close</button>
                </div>
            </div>
        </div>
    @endif
</div>

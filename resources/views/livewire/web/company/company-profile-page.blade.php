@php
    $previewLogoUrl = $logo ? $logo->temporaryUrl() : $logo_url;
@endphp

<div class="mx-auto w-full max-w-4xl p-8">
    @unless($isVerified)
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3 rounded-xl border border-amber-200 bg-gradient-to-r from-amber-50 to-amber-100/60 px-5 py-4 dark:border-amber-800/40 dark:from-amber-950/20 dark:to-amber-900/10">
            <div class="flex items-center gap-3 flex-1">
                <span class="material-symbols-outlined text-amber-600 dark:text-amber-400 shrink-0">warning</span>
                <div class="flex-1">
                    <h4 class="text-sm font-bold text-amber-800 dark:text-amber-300">Business Verification Required</h4>
                    <p class="text-xs text-amber-700/90 dark:text-amber-400/80 mt-0.5">Please submit your business verification documents to verify your organization.</p>
                </div>
            </div>
            <a href="{{ route('company.verification') }}" class="inline-flex justify-center items-center rounded-lg bg-amber-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-amber-500 transition-colors shrink-0">
                Start Verification
            </a>
        </div>
    </div>
    @endunless

    <div class="mb-8">
        <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Organization Settings</h1>
        <p class="mt-2 text-slate-500 dark:text-slate-400">
            Update your company details and how your brand appears on the platform.
        </p>
    </div>

    @if (session()->has('success'))
        <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="border-b border-slate-200 p-6 dark:border-slate-800">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">General Information</h3>
            <p class="text-sm text-slate-500">Visible to team members and on generated invoices.</p>
        </div>

        <form class="space-y-8 p-8" wire:submit="save">
            <div class="flex flex-col gap-8 border-b border-slate-100 pb-8 dark:border-slate-800 md:flex-row md:items-center">
                <div class="flex flex-col gap-1 md:w-1/3">
                    <span class="text-sm font-semibold text-slate-900 dark:text-white">Company Logo</span>
                    <span class="text-xs text-slate-500">
                        Used for profile avatar and reports. Recommended 400x400px.
                    </span>
                </div>

                <div class="flex items-center gap-6">
                    <div class="group relative">
                        <div class="flex size-24 items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-slate-300 bg-slate-100 dark:border-slate-700 dark:bg-slate-800">
                            @if ($previewLogoUrl)
                                <img src="{{ $previewLogoUrl }}" alt="Company logo preview" class="h-full w-full object-cover" />
                            @else
                                <span class="material-symbols-outlined text-3xl text-slate-400">image</span>
                            @endif
                        </div>

                        <button
                            type="button"
                            onclick="document.getElementById('company_logo_input').click()"
                            class="absolute -bottom-2 -right-2 flex size-8 items-center justify-center rounded-full border border-slate-200 bg-white text-primary shadow-lg hover:text-primary/80 dark:border-slate-700 dark:bg-slate-800"
                        >
                            <span class="material-symbols-outlined text-sm">edit</span>
                        </button>

                        <input
                            id="company_logo_input"
                            type="file"
                            accept="image/*"
                            class="hidden"
                            wire:model="logo"
                        />
                    </div>

                    <div class="flex flex-col gap-2">
                        <button
                            type="button"
                            onclick="document.getElementById('company_logo_input').click()"
                            class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white transition-all hover:bg-primary/90"
                        >
                            Upload New
                        </button>

                        <button
                            type="button"
                            wire:click="removeLogo"
                            class="rounded-lg px-4 py-2 text-sm font-medium text-slate-500 transition-all hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800"
                        >
                            Remove
                        </button>
                    </div>
                </div>
            </div>

            @error('logo')
                <p class="-mt-4 text-xs text-red-500">{{ $message }}</p>
            @enderror

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div class="flex flex-col gap-2">
                    <label for="company-name" class="text-sm font-semibold text-slate-900 dark:text-white">
                        Company Name
                    </label>
                    <input
                        id="company-name"
                        type="text"
                        wire:model="company_name"
                        placeholder="Enter company name"
                        class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                    @error('company_name')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col gap-2">
                    <label for="company-email" class="text-sm font-semibold text-slate-900 dark:text-white">
                        Contact Email
                    </label>
                    <input
                        id="company-email"
                        type="email"
                        wire:model="contact_email"
                        placeholder="Enter business email"
                        class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                    @error('contact_email')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col gap-2">
                    <label for="company-country" class="text-sm font-semibold text-slate-900 dark:text-white">
                        Country
                    </label>
                    <select
                        id="company-country"
                        wire:model="country"
                        class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    >
                        @foreach(\App\Models\Company::$countries as $code => $name)
                            <option value="{{ $code }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('country')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col gap-2 md:col-span-2">
                    <label for="company-website" class="text-sm font-semibold text-slate-900 dark:text-white">
                        Website URL
                    </label>
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-4 text-lg text-slate-400">language</span>
                        <input
                            id="company-website"
                            type="url"
                            wire:model="website_url"
                            placeholder="https://example.com"
                            class="w-full rounded-lg border border-slate-300 bg-white py-3 pl-12 pr-4 text-slate-900 outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        />
                    </div>
                    @error('website_url')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col gap-2 md:col-span-2">
                    <label for="company-bio" class="text-sm font-semibold text-slate-900 dark:text-white">
                        Description
                    </label>
                    <textarea
                        id="company-bio"
                        wire:model="description"
                        rows="4"
                        placeholder="Briefly describe your organization..."
                        class="w-full resize-none rounded-lg border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    ></textarea>
                    @error('description')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-6 dark:border-slate-800">
                <button
                    type="button"
                    wire:click="discardChanges"
                    class="rounded-lg px-6 py-2.5 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800"
                >
                    Discard Changes
                </button>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="flex items-center gap-2 rounded-lg bg-primary px-8 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/30 transition-all hover:bg-primary/90 disabled:cursor-not-allowed disabled:opacity-70"
                >
                    <span class="material-symbols-outlined text-lg" wire:loading.remove wire:target="save">save</span>
                    <span wire:loading.remove wire:target="save">Save Changes</span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
            </div>
        </form>
    </div>

    <div class="mt-8 grid grid-cols-1 gap-6 md:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
            <div class="mb-4 flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-900/30">
                    <span class="material-symbols-outlined">verified</span>
                </div>
                <h4 class="font-semibold text-slate-900 dark:text-white flex items-center gap-2">
                    <span>Verification Status</span>
                    <x-verification-badge :company="Auth::user()->company" :showText="true" />
                </h4>
            </div>
            <p class="mb-4 text-sm text-slate-500 dark:text-slate-400">
                Submit and manage your corporate business documents to achieve verified trusted status on the platform.
            </p>
            <a href="{{ route('company.verification') }}" class="text-sm font-semibold text-primary hover:underline flex items-center gap-1">
                <span>Manage verification documents</span>
                <span class="material-symbols-outlined text-[16px]">arrow_right_alt</span>
            </a>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
            <div class="mb-4 flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-900/30">
                    <span class="material-symbols-outlined">security</span>
                </div>
                <h4 class="font-semibold text-slate-900 dark:text-white">Data Privacy</h4>
            </div>
            <p class="mb-4 text-sm text-slate-500 dark:text-slate-400">
                Control how your company data is shared with third-party integrations.
            </p>
            <button type="button" class="text-sm font-medium text-primary hover:underline">
                Manage privacy settings
            </button>
        </div>
    </div>
</div>

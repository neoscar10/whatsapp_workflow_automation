<div class="mx-auto w-full max-w-[1400px] space-y-8 p-8 md:p-10">
    @php
        $total = $templates->total();
        $from = $templates->firstItem() ?? 0;
        $to = $templates->lastItem() ?? 0;
        $currentPage = $templates->currentPage();
        $lastPage = $templates->lastPage();

        $normalizedLanguageMap = [
            'en_US' => 'English (US)',
            'en_GB' => 'English (UK)',
            'es_ES' => 'Spanish (ES)',
            'fr_FR' => 'French (FR)',
            'de_DE' => 'German (DE)',
        ];
    @endphp

    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white">Templates Management</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Create and manage your WhatsApp Cloud API message templates</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button
                wire:click="syncTemplates"
                wire:loading.attr="disabled"
                type="button"
                class="inline-flex items-center gap-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-300 shadow-sm transition hover:bg-slate-50 dark:hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60"
            >
                <svg wire:loading.remove wire:target="syncTemplates" class="h-4 w-4 text-slate-500 dark:text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>

                <svg wire:loading wire:target="syncTemplates" class="h-4 w-4 animate-spin text-slate-500 dark:text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>

                Sync with Meta
            </button>

            <a
                href="{{ route('whatsapp.templates.create') }}"
                class="inline-flex items-center gap-2 rounded-lg bg-[#2463eb] px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-[#2463eb]/20 transition hover:bg-[#1f57cf]"
            >
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                </svg>
                Create Template
            </a>
        </div>
    </div>

    @if($syncMessage)
        <div class="rounded-xl border border-emerald-200 dark:border-emerald-900/30 bg-emerald-50 dark:bg-emerald-900/20 px-4 py-3 text-sm font-medium text-emerald-700 dark:text-emerald-400 shadow-sm">
            {{ $syncMessage }}
        </div>
    @endif

    @if($syncError)
        <div class="rounded-xl border border-red-200 dark:border-red-900/30 bg-red-50 dark:bg-red-900/20 px-4 py-3 text-sm font-medium text-red-700 dark:text-red-400 shadow-sm">
            {{ $syncError }}
        </div>
    @endif

    <div class="space-y-5">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div class="border-b border-slate-200 dark:border-slate-800">
                <div class="flex flex-wrap gap-8">
                    <button
                        wire:click="$set('statusFilter', '')"
                        type="button"
                        class="pb-4 text-sm {{ $statusFilter === '' ? 'border-b-2 border-[#2463eb] font-bold text-[#2463eb]' : 'font-medium text-slate-500 dark:text-slate-400 transition hover:text-slate-700 dark:hover:text-slate-300' }}"
                    >
                        All Templates
                        <span class="ml-2 rounded px-2 py-0.5 text-[10px] {{ $statusFilter === '' ? 'bg-[#2463eb]/10 text-[#2463eb]' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                            {{ $counts['all'] ?? 0 }}
                        </span>
                    </button>

                    <button
                        wire:click="$set('statusFilter', 'approved')"
                        type="button"
                        class="pb-4 text-sm {{ $statusFilter === 'approved' ? 'border-b-2 border-[#2463eb] font-bold text-[#2463eb]' : 'font-medium text-slate-500 dark:text-slate-400 transition hover:text-slate-700 dark:hover:text-slate-300' }}"
                    >
                        Approved
                        <span class="ml-2 rounded px-2 py-0.5 text-[10px] {{ $statusFilter === 'approved' ? 'bg-[#2463eb]/10 text-[#2463eb]' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                            {{ $counts['approved'] ?? 0 }}
                        </span>
                    </button>

                    <button
                        wire:click="$set('statusFilter', 'pending')"
                        type="button"
                        class="pb-4 text-sm {{ $statusFilter === 'pending' ? 'border-b-2 border-[#2463eb] font-bold text-[#2463eb]' : 'font-medium text-slate-500 dark:text-slate-400 transition hover:text-slate-700 dark:hover:text-slate-300' }}"
                    >
                        Pending
                        <span class="ml-2 rounded px-2 py-0.5 text-[10px] {{ $statusFilter === 'pending' ? 'bg-[#2463eb]/10 text-[#2463eb]' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                            {{ $counts['pending'] ?? 0 }}
                        </span>
                    </button>

                    <button
                        wire:click="$set('statusFilter', 'rejected')"
                        type="button"
                        class="pb-4 text-sm {{ $statusFilter === 'rejected' ? 'border-b-2 border-[#2463eb] font-bold text-[#2463eb]' : 'font-medium text-slate-500 dark:text-slate-400 transition hover:text-slate-700 dark:hover:text-slate-300' }}"
                    >
                        Rejected
                        <span class="ml-2 rounded px-2 py-0.5 text-[10px] {{ $statusFilter === 'rejected' ? 'bg-[#2463eb]/10 text-[#2463eb]' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                            {{ $counts['rejected'] ?? 0 }}
                        </span>
                    </button>
                </div>
            </div>

            <div class="flex w-full flex-col gap-3 sm:flex-row xl:w-auto">
                <div class="relative w-full sm:w-[320px]">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400 dark:text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                    </svg>
                    <input
                        wire:model.live.debounce.300ms="search"
                        type="search"
                        placeholder="Search templates..."
                        class="h-11 w-full rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 pl-10 pr-4 text-sm text-slate-900 dark:text-white shadow-sm outline-none transition placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-[#2463eb] dark:focus:border-[#2463eb] focus:ring-2 focus:ring-[#2463eb]/20"
                    >
                </div>

                <div class="w-full sm:w-[200px]">
                    <select
                        wire:model.live="categoryFilter"
                        class="h-11 w-full rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-4 pr-10 text-sm text-slate-700 dark:text-slate-300 shadow-sm outline-none transition focus:border-[#2463eb] dark:focus:border-[#2463eb] focus:ring-2 focus:ring-[#2463eb]/20"
                    >
                        <option value="">All Categories</option>
                        <option value="marketing">Marketing</option>
                        <option value="utility">Utility</option>
                        <option value="authentication">Authentication</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50">
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Template Name</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Category</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Language</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Status</th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        @forelse($templates as $template)
                            @php
                                $category = strtolower((string) $template->category);
                                $status = strtolower((string) $template->status);
                                $normalizedCode = str_replace('-', '_', (string) $template->language_code);
                                $languageLabel = $normalizedLanguageMap[$normalizedCode] ?? strtoupper((string) $template->language_code);

                                $categoryClasses = match ($category) {
                                    'marketing' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                    'authentication' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
                                    'utility' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
                                    default => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
                                };
                            @endphp

                            <tr class="transition hover:bg-slate-50/70 dark:hover:bg-slate-800/50">
                                <td class="px-6 py-5">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-slate-900 dark:text-white">
                                            {{ $template->display_title ?: $template->remote_template_name }}
                                        </span>
                                        <span class="mt-0.5 text-xs italic text-slate-400 dark:text-slate-500">
                                            Updated {{ $template->updated_at?->diffForHumans() ?: 'Never' }}
                                        </span>
                                    </div>
                                </td>

                                <td class="px-6 py-5">
                                    <span class="rounded px-2.5 py-1 text-xs font-medium {{ $categoryClasses }}">
                                        {{ ucfirst((string) $template->category) }}
                                    </span>
                                </td>

                                <td class="px-6 py-5 text-sm text-slate-600 dark:text-slate-300">
                                    {{ $languageLabel }}
                                </td>

                                <td class="px-6 py-5">
                                    @if($status === 'approved')
                                        <div class="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400">
                                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                            <span class="text-xs font-bold uppercase tracking-wide">Approved</span>
                                        </div>
                                    @elseif($status === 'rejected')
                                        <div class="flex items-center gap-1.5 text-red-500 dark:text-red-400">
                                            <span class="h-2 w-2 rounded-full bg-red-500"></span>
                                            <span class="text-xs font-bold uppercase tracking-wide">Rejected</span>
                                        </div>
                                    @elseif(in_array($status, ['pending', 'in_appeal']))
                                        <div class="flex items-center gap-1.5 text-amber-500 dark:text-amber-400">
                                            <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                                            <span class="text-xs font-bold uppercase tracking-wide">Pending</span>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-1.5 text-slate-500 dark:text-slate-400">
                                            <span class="h-2 w-2 rounded-full bg-slate-400 dark:bg-slate-500"></span>
                                            <span class="text-xs font-bold uppercase tracking-wide">
                                                {{ strtoupper((string) ($template->status ?: 'draft')) }}
                                            </span>
                                        </div>
                                    @endif
                                </td>

                                <td class="px-6 py-5 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('whatsapp.templates.show', $template->id) }}" class="rounded p-1.5 text-slate-400 dark:text-slate-500 transition hover:text-[#2463eb] dark:hover:text-[#2463eb]" title="View">
                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        </a>

                                        <a href="{{ route('whatsapp.templates.edit', $template->id) }}" class="rounded p-1.5 text-slate-400 dark:text-slate-500 transition hover:text-[#2463eb] dark:hover:text-[#2463eb]" title="Edit">
                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                            </svg>
                                        </a>

                                        <button
                                            wire:click="confirmDelete({{ $template->id }})"
                                            type="button"
                                            class="rounded p-1.5 text-slate-400 dark:text-slate-500 transition hover:text-red-500 dark:hover:text-red-400"
                                            title="Delete"
                                        >
                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16">
                                    <div class="flex flex-col items-center justify-center text-center">
                                        <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800">
                                            <svg class="h-7 w-7 text-slate-400 dark:text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                            </svg>
                                        </div>
                                        <h3 class="text-base font-bold text-slate-900 dark:text-white">No templates found</h3>
                                        <p class="mt-1 max-w-md text-sm text-slate-500 dark:text-slate-400">
                                            Adjust your filters or sync with Meta to see templates.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/30 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                <span class="text-sm text-slate-500 dark:text-slate-400">
                    Showing {{ $from }} to {{ $to }} of {{ $total }} results
                </span>

                @if($templates->hasPages())
                    <div class="flex flex-wrap items-center gap-2">
                        <button
                            wire:click="previousPage"
                            @disabled($templates->onFirstPage())
                            type="button"
                            class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-1.5 text-sm font-medium text-slate-700 dark:text-slate-300 transition hover:bg-slate-100 dark:hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Previous
                        </button>

                        @for($page = 1; $page <= $lastPage; $page++)
                            <button
                                wire:click="gotoPage({{ $page }})"
                                type="button"
                                class="rounded-lg px-3 py-1.5 text-sm font-medium transition {{ $currentPage === $page ? 'bg-[#2463eb] text-white shadow-sm border border-[#2463eb]' : 'border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}"
                            >
                                {{ $page }}
                            </button>
                        @endfor

                        <button
                            wire:click="nextPage"
                            @disabled(!$templates->hasMorePages())
                            type="button"
                            class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-1.5 text-sm font-medium text-slate-700 dark:text-slate-300 transition hover:bg-slate-100 dark:hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Next
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <span class="text-sm font-bold text-slate-500 dark:text-slate-400">Template Usage</span>
                <svg class="h-5 w-5 text-[#2463eb]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18M7 14l3-3 3 3 5-6" />
                </svg>
            </div>
            <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $counts['all'] ?? 0 }}</p>
            <div class="mt-2 text-xs font-medium text-slate-500 dark:text-slate-400">Total Templates</div>
        </div>

        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <span class="text-sm font-bold text-slate-500 dark:text-slate-400">Delivery Rate</span>
                <svg class="h-5 w-5 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m6 2.25a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="text-2xl font-black text-slate-900 dark:text-white">N/A</p>
            <div class="mt-2 text-xs text-slate-400 dark:text-slate-500">Awaiting sufficient delivery data</div>
        </div>

        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <span class="text-sm font-bold text-slate-500 dark:text-slate-400">Read Rate</span>
                <svg class="h-5 w-5 text-amber-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <p class="text-2xl font-black text-slate-900 dark:text-white">N/A</p>
            <div class="mt-2 text-xs text-slate-400 dark:text-slate-500">Awaiting sufficient read data</div>
        </div>
    </div>

    @if($templateToDelete)
        <div class="relative z-[9999]" aria-labelledby="modal-title" role="dialog" aria-modal="true" wire:keydown.escape.window="cancelDelete">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" wire:click="cancelDelete"></div>

            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-slate-200 dark:border-slate-800 dark:bg-slate-900">
                        <div class="px-6 py-6">
                            <div class="flex items-start gap-4">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                                    <svg class="h-5 w-5 text-red-600 dark:text-red-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2.25m0 3.75h.008v.008H12v-.008z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 3.94L1.82 18a2.25 2.25 0 001.924 3.375h16.512A2.25 2.25 0 0022.18 18L13.66 3.94a2.25 2.25 0 00-3.32 0z" />
                                    </svg>
                                </div>

                                <div class="flex-1">
                                    <h3 class="text-lg font-bold text-slate-900 dark:text-white" id="modal-title">Delete Template</h3>
                                    <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
                                        Are you sure you want to delete this template? This will remove it from formatting and from your Meta WhatsApp Business Account. This action cannot be undone.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col-reverse gap-3 border-t border-slate-100 bg-slate-50 dark:border-slate-800/60 dark:bg-slate-800/50 px-6 py-4 sm:flex-row sm:justify-end">
                            <button
                                wire:click="cancelDelete"
                                type="button"
                                class="inline-flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-300 shadow-sm transition hover:bg-slate-50 dark:hover:bg-slate-700"
                            >
                                Cancel
                            </button>

                            <button
                                wire:click="deleteTemplate"
                                type="button"
                                class="inline-flex items-center justify-center rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-500"
                            >
                                <svg wire:loading wire:target="deleteTemplate" class="mr-2 h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
<div class="mx-auto w-full max-w-[1400px] space-y-8 p-10">
    {{-- Header Section --}}
    <div class="flex items-center justify-between gap-4 border-b border-slate-200 pb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('whatsapp.templates.show', $template->id) }}" class="group flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 hover:bg-slate-200 transition-colors">
                <svg class="h-5 w-5 text-slate-500 group-hover:text-slate-700" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                    Edit: {{ $template->display_title }}
                    <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-1 text-sm font-medium text-slate-600 ring-1 ring-inset ring-slate-500/10 capitalize">{{ $template->status }}</span>
                </h1>
                <p class="mt-2 text-sm text-slate-500">Modify your template content and resubmit to Meta.</p>
            </div>
        </div>
        
        <div>
            <a href="{{ route('whatsapp.templates.show', $template->id) }}" class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">
                Cancel Edit
            </a>
        </div>
    </div>

    @if (session()->has('warning'))
        <div class="rounded-xl bg-yellow-50 p-4 border border-yellow-200">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-yellow-800">{{ session('warning') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->has('api'))
        <div class="rounded-xl bg-red-50 p-4 border border-red-200">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800">{{ $errors->first('api') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        {{-- Left Column: Form --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 space-y-8">
                    
                    {{-- Basic Info (Name and Language are read-only per Meta API) --}}
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Basic Details</h3>
                            <p class="text-sm text-slate-500">Name and language cannot be changed once a template is created on Meta.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-slate-900">Template Name</label>
                                <div class="mt-2">
                                    <input type="text" value="{{ $name }}" disabled class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-slate-500 shadow-sm sm:text-sm">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-900">Language</label>
                                <div class="mt-2">
                                     <input type="text" value="{{ strtoupper($language) }}" disabled class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-slate-500 shadow-sm sm:text-sm">
                                </div>
                            </div>

                            <div class="md:col-span-2">
                                <label for="category" class="block text-sm font-semibold text-slate-900">Category</label>
                                <div class="mt-2">
                                    <select wire:model="category" id="category" class="block w-full rounded-xl border-0 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6 bg-white">
                                        <option value="marketing">Marketing</option>
                                        <option value="utility">Utility</option>
                                        <option value="authentication">Authentication</option>
                                    </select>
                                </div>
                                @error('category') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <hr class="border-slate-200">

                    {{-- Content --}}
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Message Content</h3>
                            <p class="text-sm text-slate-500">Update the structure of your message. Use @{{1}} to insert variables.</p>
                        </div>

                        {{-- Header --}}
                        <div class="p-5 rounded-xl border-2 border-slate-100 bg-slate-50/50 space-y-4">
                            <div class="flex items-center justify-between">
                                <label class="block text-sm font-semibold text-slate-900">Header <span class="text-slate-400 font-normal">(Optional)</span></label>
                                <select wire:model.live="headerType" class="rounded-lg border-0 py-1.5 pl-3 pr-8 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm bg-white">
                                    <option value="none">None</option>
                                    <option value="text">Text</option>
                                    <option value="image">Image Media</option>
                                    <option value="video">Video Media</option>
                                    <option value="document">Document Media</option>
                                </select>
                            </div>

                            @if($headerType === 'text')
                                <div>
                                    <input wire:model.live.debounce.300ms="headerText" type="text" class="block w-full rounded-xl border-0 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6" placeholder="Enter header text (Max 60 chars)">
                                    @error('headerText') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                                    
                                    @if(str_contains($headerText ?? '', '{' . '{1}}'))
                                        <div class="mt-3">
                                            <label class="block text-xs font-semibold text-slate-600">Sample for @{{1}}</label>
                                            <input wire:model="exampleHeaderValues.0" type="text" class="mt-1 block w-full rounded-lg border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 text-sm" placeholder="e.g. John">
                                        </div>
                                    @endif
                                </div>
                            @elseif($headerType !== 'none')
                                <div class="rounded-lg bg-orange-50 p-4 border border-orange-200">
                                    <p class="text-sm text-orange-800">Media headers require a valid Resumable Upload API handle. A placeholder will be submitted for testing.</p>
                                </div>
                            @endif
                        </div>

                        {{-- Body --}}
                        <div class="p-5 rounded-xl border-2 border-slate-100 bg-slate-50/50 space-y-4">
                            <label class="block text-sm font-semibold text-slate-900">Body <span class="text-red-500">*</span></label>
                            <div class="mt-2">
                                <textarea wire:model.live.debounce.500ms="bodyText" rows="6" class="block w-full rounded-xl border-0 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6" placeholder="Hi @{{1}}, your order @{{2}} is ready!"></textarea>
                            </div>
                            @error('bodyText') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror

                            @if(count($exampleBodyValues) > 0)
                                <div class="mt-4 p-4 bg-white rounded-xl border border-slate-200 space-y-3">
                                    <h4 class="text-sm font-semibold text-slate-800">Sample Values for Meta Validation</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        @foreach($exampleBodyValues as $index => $val)
                                            <div wire:key="body-val-{{ $index }}">
                                                <label class="block text-xs font-semibold text-slate-600">Sample for @{{ {{ $index + 1 }} }}</label>
                                                <input wire:model="exampleBodyValues.{{ $index }}" type="text" class="mt-1 block w-full rounded-lg border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 text-sm">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Footer --}}
                        <div class="p-5 rounded-xl border-2 border-slate-100 bg-slate-50/50 space-y-4">
                            <label class="block text-sm font-semibold text-slate-900">Footer <span class="text-slate-400 font-normal">(Optional)</span></label>
                            <input wire:model.live.debounce.300ms="footerText" type="text" class="block w-full rounded-xl border-0 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6">
                            @error('footerText') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                    </div>

                    <hr class="border-slate-200">

                    {{-- Buttons --}}
                    <div class="space-y-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-bold text-slate-900">Interactive Buttons <span class="text-slate-400 font-normal">(Optional)</span></h3>
                            </div>
                            
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" @click.away="open = false" type="button" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                    Add Button
                                </button>
                                
                                <div x-show="open" style="display: none;" class="absolute right-0 z-10 mt-2 w-48 rounded-xl bg-white p-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                                    <button wire:click="addButton('quick_reply')" @click="open = false" class="block w-full text-left rounded-lg px-4 py-2 text-sm text-slate-700 hover:bg-slate-100 hover:text-slate-900">Quick Reply</button>
                                    <button wire:click="addButton('url')" @click="open = false" class="block w-full text-left rounded-lg px-4 py-2 text-sm text-slate-700 hover:bg-slate-100 hover:text-slate-900">Visit Website</button>
                                    <button wire:click="addButton('phone_number')" @click="open = false" class="block w-full text-left rounded-lg px-4 py-2 text-sm text-slate-700 hover:bg-slate-100 hover:text-slate-900">Call Phone</button>
                                </div>
                            </div>
                        </div>
                        
                        @error('buttons') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror

                        @if(count($buttons) > 0)
                            <div class="space-y-4">
                                @foreach($buttons as $index => $button)
                                    <div class="bg-white border rounded-xl border-slate-200 p-4 shadow-sm flex items-start gap-4" wire:key="button-{{ $index }}">
                                        <div class="mt-2 p-2 bg-slate-100 rounded-lg shrink-0">
                                            @if($button['type'] === 'quick_reply')
                                                <svg class="h-5 w-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" /></svg>
                                            @elseif($button['type'] === 'url')
                                                <svg class="h-5 w-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                            @elseif($button['type'] === 'phone_number')
                                                <svg class="h-5 w-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                            @endif
                                        </div>
                                        
                                        <div class="flex-1 space-y-4">
                                            <div>
                                                <label class="block text-xs font-semibold text-slate-600">Button Text</label>
                                                <input wire:model.live.debounce.300ms="buttons.{{ $index }}.text" type="text" class="mt-1 block w-full rounded-lg border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm">
                                                @error("buttons.{$index}.text") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                            </div>
                                            
                                            @if($button['type'] === 'url')
                                                <div>
                                                    <label class="block text-xs font-semibold text-slate-600">Website URL</label>
                                                    <input wire:model.live.debounce.300ms="buttons.{{ $index }}.url" type="text" class="mt-1 block w-full rounded-lg border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm" placeholder="https://example.com/order/@{{1}}">
                                                    @error("buttons.{$index}.url") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                                    
                                                    @if(str_contains($button['url'] ?? '', '{' . '{1}}'))
                                                        <div class="mt-2">
                                                            <label class="block text-xs font-semibold text-slate-600">Sample URL Path</label>
                                                            <input wire:model="buttons.{{ $index }}.example_value" type="text" class="mt-1 block w-full rounded-lg border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm">
                                                        </div>
                                                    @endif
                                                </div>
                                            @elseif($button['type'] === 'phone_number')
                                                <div>
                                                    <label class="block text-xs font-semibold text-slate-600">Phone Number</label>
                                                    <input wire:model="buttons.{{ $index }}.phone_number" type="text" class="mt-1 block w-full rounded-lg border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm">
                                                    @error("buttons.{$index}.phone_number") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <button wire:click="removeButton({{ $index }})" type="button" class="shrink-0 p-2 text-slate-400 hover:text-red-500 rounded-lg hover:bg-slate-100 transition-colors">
                                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd" /></svg>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="rounded-xl border border-dashed border-slate-300 p-8 text-center bg-slate-50">
                                <span class="block text-sm text-slate-500">No buttons added.</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="bg-gray-50 border-t border-slate-200 px-6 py-4 flex items-center justify-end gap-3">
                    <button wire:click="updateTemplate" wire:loading.attr="disabled" type="button" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 transition-all disabled:opacity-50">
                        <svg wire:loading wire:target="updateTemplate" class="h-4 w-4 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Update & Resubmit
                    </button>
                </div>
            </div>
        </div>

        {{-- Right Column: Live Preview --}}
        <div class="lg:col-span-1 border-4 border-slate-900/10 rounded-[2.5rem] bg-slate-50 relative h-auto shadow-2xl p-4 overflow-hidden sticky top-8">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-32 h-6 bg-slate-900/10 rounded-b-2xl"></div>
            
            <div class="bg-[#efeae2] rounded-3xl h-full shadow-inner overflow-hidden flex flex-col pt-12">
                <div class="bg-[#008069] text-white p-3 flex items-center gap-3 shadow-sm z-10 shrink-0">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-slate-200"></div>
                        <div class="font-semibold text-sm">Preview</div>
                    </div>
                </div>

                <div class="flex-1 p-4 overflow-y-auto space-y-4 pb-20 no-scrollbar">
                    
                    <div class="bg-[#d9fdd3] p-3 rounded-lg rounded-tr-none shadow-sm max-w-[85%] ml-auto text-sm text-slate-800">
                        Trigger Edit Simulation
                        <div class="text-[10px] text-slate-500 mt-1 text-right">Just now</div>
                    </div>

                    <div class="bg-white p-2 rounded-lg rounded-tl-none shadow-sm max-w-[90%] space-y-2">
                        @if($headerType && $headerType !== 'none')
                            <div class="font-bold text-slate-900 text-sm px-1 pt-1">
                                @if($headerType === 'text')
                                    {{ $headerText ?: 'Your Header Here' }}
                                @else
                                    <div class="w-full h-32 bg-slate-200 rounded animate-pulse flex items-center justify-center text-slate-400">
                                        [{{ strtoupper($headerType) }}]
                                    </div>
                                @endif
                            </div>
                        @endif
                        
                        <div class="px-1 text-sm text-slate-800 leading-relaxed whitespace-pre-wrap">
                            @if(empty($bodyText))
                                <span class="text-slate-400 italic">Your message body will appear here...</span>
                            @else
                                {{ $bodyText }}
                            @endif
                        </div>
                        
                        @if($footerText)
                            <div class="px-1 text-[11px] text-slate-500 mt-1">{{ $footerText }}</div>
                        @endif

                        @if(count($buttons) > 0)
                            <div class="space-y-1.5 pt-2 mt-2 border-t border-slate-100">
                                @foreach($buttons as $btn)
                                    <div class="w-full text-center text-[#00a884] font-semibold text-sm py-2">
                                        @if($btn['type'] === 'url')
                                            <svg class="inline-block h-4 w-4 mr-1 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                        @elseif($btn['type'] === 'phone_number')
                                            <svg class="inline-block h-4 w-4 mr-1 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                        @elseif($btn['type'] === 'quick_reply')
                                            <svg class="inline-block h-4 w-4 mr-1 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" /></svg>
                                        @endif
                                        {{ $btn['text'] ?: 'Button Label' }}
                                    </div>
                                    @if(!$loop->last)
                                        <hr class="border-slate-100">
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

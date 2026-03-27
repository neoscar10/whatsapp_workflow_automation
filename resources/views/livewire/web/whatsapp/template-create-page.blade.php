<div class="mx-auto w-full max-w-[1400px] space-y-8 p-10">
    {{-- Header Section --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('whatsapp.templates.index') }}" class="group flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
            <svg class="h-5 w-5 text-slate-500 dark:text-slate-400 group-hover:text-slate-700 dark:group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
        </a>
        <div>
            <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">Create Template</h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Design your WhatsApp message template and submit it for Meta approval.</p>
        </div>
    </div>

    @if ($errors->has('api'))
        <div class="rounded-xl bg-red-50 dark:bg-red-900/20 p-4 border border-red-200 dark:border-red-900/30">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400 dark:text-red-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800 dark:text-red-400">{{ $errors->first('api') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        {{-- Left Column: Form --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="p-6 space-y-8">
                    
                    {{-- Basic Info --}}
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Basic Details</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Define the core attributes of your template.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-semibold text-slate-900 dark:text-white">Template Name</label>
                                <div class="mt-2">
                                    <input wire:model="name" type="text" id="name" class="block w-full rounded-xl border-0 px-4 py-2.5 text-slate-900 dark:text-white dark:bg-slate-800 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-700 placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6" placeholder="e.g. order_confirmation_v1">
                                </div>
                                @error('name') <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Lowercase letters, numbers, and underscores only.</p>
                            </div>

                            <div>
                                <label for="category" class="block text-sm font-semibold text-slate-900 dark:text-white">Category</label>
                                <div class="mt-2">
                                    <select wire:model="category" id="category" class="block w-full rounded-xl border-0 pl-4 pr-10 py-2.5 text-slate-900 dark:text-white bg-white dark:bg-slate-800 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6">
                                        <option value="marketing">Marketing (Promos, offers, updates)</option>
                                        <option value="utility">Utility (Order updates, alerts)</option>
                                        <option value="authentication">Authentication (OTPs)</option>
                                    </select>
                                </div>
                                @error('category') <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="language" class="block text-sm font-semibold text-slate-900 dark:text-white">Language</label>
                                <div class="mt-2">
                                    <select wire:model="language" id="language" class="block w-full rounded-xl border-0 pl-4 pr-10 py-2.5 text-slate-900 dark:text-white bg-white dark:bg-slate-800 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6">
                                        <option value="en_US">English (US)</option>
                                        <option value="en_GB">English (UK)</option>
                                        <option value="es">Spanish</option>
                                        <option value="fr">French</option>
                                    </select>
                                </div>
                                @error('language') <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <hr class="border-slate-200 dark:border-slate-800">

                    {{-- Content --}}
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Message Content</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Design the structure of your message. Use @{{1}} to insert variables.</p>
                        </div>

                        {{-- Header --}}
                        <div class="p-5 rounded-xl border-2 border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 space-y-4 transition-colors focus-within:border-primary-200 focus-within:bg-primary-50 dark:focus-within:border-primary-900/50 dark:focus-within:bg-primary-900/10">
                            <div class="flex items-center justify-between">
                                <label class="block text-sm font-semibold text-slate-900 dark:text-white">Header <span class="text-slate-400 dark:text-slate-500 font-normal">(Optional)</span></label>
                                <select wire:model.live="headerType" class="rounded-lg border-0 py-1.5 pl-4 pr-10 text-slate-900 dark:text-white shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-700 bg-white dark:bg-slate-800 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm">
                                    <option value="none">None</option>
                                    <option value="text">Text</option>
                                    <option value="image">Image Media</option>
                                    <option value="video">Video Media</option>
                                    <option value="document">Document Media</option>
                                </select>
                            </div>

                            @if($headerType === 'text')
                                <div>
                                    <input wire:model.live.debounce.300ms="headerText" type="text" class="block w-full rounded-xl border-0 px-4 py-2.5 text-slate-900 dark:text-white bg-white dark:bg-slate-800 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-700 placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6" placeholder="Enter header text (Max 60 chars)">
                                    @error('headerText') <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                    
                                    @if(str_contains($headerText ?? '', '{' . '{1}}'))
                                        <div class="mt-3">
                                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400">Sample for @{{1}}</label>
                                            <input wire:model="exampleHeaderValues.0" type="text" class="mt-1 block w-full rounded-lg border-0 px-3 py-1.5 text-slate-900 dark:text-white bg-white dark:bg-slate-800 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-primary-600 text-sm" placeholder="e.g. John">
                                        </div>
                                    @endif
                                </div>
                            @elseif($headerType !== 'none')
                                 <div class="space-y-4">
                                    <div class="flex items-center justify-center w-full">
                                        <label for="headerSampleFile" class="flex flex-col items-center justify-center w-full h-32 border-2 border-slate-300 border-dashed rounded-xl cursor-pointer bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                                <svg class="w-8 h-8 mb-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                </svg>
                                                <p class="mb-2 text-sm text-slate-500 dark:text-slate-400"><span class="font-semibold">Click to upload</span> sample {{ $headerType }}</p>
                                                <p class="text-xs text-slate-400">Meta requires a sample for review</p>
                                            </div>
                                            <input wire:model="headerSampleFile" id="headerSampleFile" type="file" class="hidden" 
                                                @if($headerType === 'image') accept="image/*" 
                                                @elseif($headerType === 'video') accept="video/*" 
                                                @elseif($headerType === 'document') accept=".pdf,.doc,.docx" @endif />
                                        </label>
                                    </div>

                                    @if($headerSampleFile)
                                        <div class="flex items-center justify-between p-3 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                                            <div class="flex items-center gap-3">
                                                @if($headerType === 'image' && !is_string($headerSampleFile))
                                                    <img src="{{ $headerSampleFile->temporaryUrl() }}" class="h-10 w-10 rounded object-cover">
                                                @else
                                                    <div class="flex h-10 w-10 items-center justify-center rounded bg-slate-100 dark:bg-slate-700 text-slate-500">
                                                        <span class="material-symbols-outlined text-[20px]">
                                                            {{ $headerType === 'video' ? 'movie' : 'description' }}
                                                        </span>
                                                    </div>
                                                @endif
                                                <div>
                                                    <p class="text-xs font-bold text-slate-900 dark:text-white truncate max-w-[200px]">{{ is_string($headerSampleFile) ? $headerSampleFile : $headerSampleFile->getClientOriginalName() }}</p>
                                                    <p class="text-[10px] text-slate-500 uppercase">{{ $headerType }} handle ready</p>
                                                </div>
                                            </div>
                                            <button type="button" wire:click="$set('headerSampleFile', null)" class="text-slate-400 hover:text-red-500 transition-colors">
                                                <span class="material-symbols-outlined text-[20px]">close</span>
                                            </button>
                                        </div>
                                    @endif

                                    @error('headerSampleFile') <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                    
                                    <div class="rounded-lg bg-primary-50 dark:bg-primary-900/10 p-3 border border-primary-100 dark:border-primary-900/20">
                                        <p class="text-[11px] text-primary-700 dark:text-primary-400 leading-tight">
                                            <strong>Meta Review Requirement:</strong> You must upload a sample {{ $headerType }} that represents the content you will send. Meta uses this to approve your template.
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Body --}}
                        <div class="p-5 rounded-xl border-2 border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 space-y-4 transition-colors focus-within:border-primary-200 focus-within:bg-primary-50 dark:focus-within:border-primary-900/50 dark:focus-within:bg-primary-900/10">
                            <label class="block text-sm font-semibold text-slate-900 dark:text-white">Body <span class="text-red-500">*</span></label>
                            <div class="mt-2">
                                <textarea wire:model.live.debounce.500ms="bodyText" rows="6" class="block w-full rounded-xl border-0 px-4 py-3 text-slate-900 dark:text-white bg-white dark:bg-slate-800 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-700 placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6" placeholder="Hi @{{1}}, your order @{{2}} is ready!"></textarea>
                            </div>
                            @error('bodyText') <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror

                            @if(count($exampleBodyValues) > 0)
                                <div class="mt-4 p-4 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 space-y-3">
                                    <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-200">Sample Values for Meta Validation</h4>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Meta requires examples for every variable to approve the template.</p>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        @foreach($exampleBodyValues as $index => $val)
                                            <div wire:key="body-val-{{ $index }}">
                                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300">Sample for @{{ {{ $index + 1 }} }}</label>
                                                <input wire:model="exampleBodyValues.{{ $index }}" type="text" class="mt-1 block w-full rounded-lg border-0 px-3 py-1.5 text-slate-900 dark:text-white bg-white dark:bg-slate-800 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-primary-600 text-sm" placeholder="e.g. Value {{ $index + 1 }}">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Footer --}}
                        <div class="p-5 rounded-xl border-2 border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 space-y-4 transition-colors focus-within:border-primary-200 focus-within:bg-primary-50 dark:focus-within:border-primary-900/50 dark:focus-within:bg-primary-900/10">
                            <label class="block text-sm font-semibold text-slate-900 dark:text-white">Footer <span class="text-slate-400 dark:text-slate-500 font-normal">(Optional)</span></label>
                            <input wire:model.live.debounce.300ms="footerText" type="text" class="block w-full rounded-xl border-0 px-4 py-2.5 text-slate-900 dark:text-white bg-white dark:bg-slate-800 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-700 placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6" placeholder="Enter short footer text (Max 60 chars)">
                            @error('footerText') <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        </div>

                    </div>

                    <hr class="border-slate-200 dark:border-slate-800">

                    {{-- Buttons --}}
                    <div class="space-y-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Interactive Buttons <span class="text-slate-400 dark:text-slate-500 font-normal">(Optional)</span></h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Add Call-To-Action or Quick Reply buttons.</p>
                            </div>
                            
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" @click.away="open = false" type="button" class="inline-flex items-center gap-2 rounded-xl bg-white dark:bg-slate-800 px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-200 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                    Add Button
                                </button>
                                
                                <div x-show="open" style="display: none;" class="absolute right-0 z-10 mt-2 w-48 rounded-xl bg-white dark:bg-slate-800 p-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none dark:border-slate-700 dark:border">
                                    <button wire:click="addButton('quick_reply')" @click="open = false" class="block w-full text-left rounded-lg px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white">Quick Reply</button>
                                    <button wire:click="addButton('url')" @click="open = false" class="block w-full text-left rounded-lg px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white">Visit Website</button>
                                    <button wire:click="addButton('phone_number')" @click="open = false" class="block w-full text-left rounded-lg px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white">Call Phone</button>
                                </div>
                            </div>
                        </div>
                        
                        @error('buttons') <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror

                        @if(count($buttons) > 0)
                            <div class="space-y-4">
                                @foreach($buttons as $index => $button)
                                    <div class="bg-white dark:bg-slate-900 border rounded-xl border-slate-200 dark:border-slate-800 p-4 shadow-sm flex items-start gap-4" wire:key="button-{{ $index }}">
                                        <div class="mt-2 p-2 bg-slate-100 dark:bg-slate-800/50 rounded-lg shrink-0">
                                            @if($button['type'] === 'quick_reply')
                                                <svg class="h-5 w-5 text-slate-600 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" /></svg>
                                            @elseif($button['type'] === 'url')
                                                <svg class="h-5 w-5 text-slate-600 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                            @elseif($button['type'] === 'phone_number')
                                                <svg class="h-5 w-5 text-slate-600 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                            @endif
                                        </div>
                                        
                                        <div class="flex-1 space-y-4">
                                            <div>
                                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400">Button Text</label>
                                                <input wire:model.live.debounce.300ms="buttons.{{ $index }}.text" type="text" class="mt-1 block w-full rounded-lg border-0 px-3 py-1.5 text-slate-900 dark:text-white bg-white dark:bg-slate-800 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm" placeholder="e.g. Shop Now">
                                                @error("buttons.{$index}.text") <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                            </div>
                                            
                                            @if($button['type'] === 'url')
                                                <div>
                                                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400">Website URL (Dynamic @{{1}} allowed at end)</label>
                                                    <input wire:model.live.debounce.300ms="buttons.{{ $index }}.url" type="text" class="mt-1 block w-full rounded-lg border-0 px-3 py-1.5 text-slate-900 dark:text-white bg-white dark:bg-slate-800 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm" placeholder="https://example.com/order/@{{1}}">
                                                    @error("buttons.{$index}.url") <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                                    
                                                    @if(str_contains($button['url'] ?? '', '{' . '{1}}'))
                                                        <div class="mt-2">
                                                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400">Sample URL Path</label>
                                                            <input wire:model="buttons.{{ $index }}.example_value" type="text" class="mt-1 block w-full rounded-lg border-0 px-3 py-1.5 text-slate-900 dark:text-white bg-white dark:bg-slate-800 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm" placeholder="e.g. abcd-1234">
                                                        </div>
                                                    @endif
                                                </div>
                                            @elseif($button['type'] === 'phone_number')
                                                <div>
                                                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400">Phone Number (Include country code)</label>
                                                    <input wire:model="buttons.{{ $index }}.phone_number" type="text" class="mt-1 block w-full rounded-lg border-0 px-3 py-1.5 text-slate-900 dark:text-white bg-white dark:bg-slate-800 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm" placeholder="+1234567890">
                                                    @error("buttons.{$index}.phone_number") <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <button wire:click="removeButton({{ $index }})" type="button" class="shrink-0 p-2 text-slate-400 hover:text-red-500 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd" /></svg>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="rounded-xl border border-dashed border-slate-300 dark:border-slate-700 p-8 text-center bg-slate-50 dark:bg-slate-800/30">
                                <svg class="mx-auto h-10 w-10 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                                </svg>
                                <span class="mt-2 block text-sm font-semibold text-slate-900 dark:text-white">No buttons added</span>
                                <span class="mt-1 block text-sm text-slate-500 dark:text-slate-400">Buttons increase engagement significantly.</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-800 px-6 py-4 flex items-center justify-end gap-3">
                    <button wire:click="createTemplate" wire:loading.attr="disabled" type="button" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 transition-all disabled:opacity-50">
                        <svg wire:loading wire:target="createTemplate" class="h-4 w-4 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Submit Template to Meta
                    </button>
                </div>
            </div>
        </div>

        {{-- Right Column: Live Preview --}}
        <div class="lg:col-span-1 border-4 border-slate-900/10 dark:border-white/10 rounded-[2.5rem] bg-slate-50 relative h-auto shadow-2xl p-4 overflow-hidden sticky top-8">
            {{-- Top notch --}}
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-32 h-6 bg-slate-900/10 dark:bg-white/10 rounded-b-2xl"></div>
            
            <div class="bg-[#efeae2] rounded-3xl h-full shadow-inner overflow-hidden flex flex-col pt-12">
                {{-- Header bar --}}
                <div class="bg-[#008069] text-white p-3 flex items-center gap-3 shadow-sm z-10 shrink-0">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-slate-200/50"></div>
                        <div class="font-semibold text-sm">Preview</div>
                    </div>
                </div>

                {{-- Chat background texture (simulated with bg color) --}}
                <div class="flex-1 p-4 overflow-y-auto space-y-4 pb-20 no-scrollbar">
                    
                    {{-- User message --}}
                    <div class="bg-[#d9fdd3] p-3 rounded-lg rounded-tr-none shadow-sm max-w-[85%] ml-auto text-sm text-slate-800">
                        Please send me the details.
                        <div class="text-[10px] text-slate-500 mt-1 text-right">10:00 AM</div>
                    </div>

                    {{-- Template Preview Bubble --}}
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

                        {{-- Buttons --}}
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
